<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kandidat;
use App\Models\KandidatPrilozenaDokumenta;
use App\Models\PrilozenaDokumenta;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentReviewService
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getIncompleteCandidatesReviewList(): array
    {
        return Kandidat::query()
            ->with([
                'program',
                'tipStudija',
                'godinaStudija',
                'kandidatDokumenta.dokument',
                'kandidatDokumenta.reviewer',
            ])
            ->orderBy('prezimeKandidata')
            ->orderBy('imeKandidata')
            ->get()
            ->map(fn (Kandidat $kandidat) => $this->buildCandidateReviewRow($kandidat))
            ->filter(fn (array $row) => ! $row['is_complete'])
            ->values()
            ->all();
    }

    public function getReviewPageData(Kandidat $kandidat): array
    {
        $kandidat->load([
            'program',
            'tipStudija',
            'godinaStudija',
            'kandidatDokumenta.dokument',
            'kandidatDokumenta.reviewer',
        ]);

        $dokumenta = $kandidat->kandidatDokumenta
            ->sortBy([
                fn (KandidatPrilozenaDokumenta $dokument) => $dokument->dokument?->skolskaGodina_id,
                fn (KandidatPrilozenaDokumenta $dokument) => $dokument->dokument?->redniBrojDokumenta,
                fn (KandidatPrilozenaDokumenta $dokument) => $dokument->id,
            ])
            ->values();

        return [
            'kandidat' => $kandidat,
            'dokumenta' => $dokumenta,
            'summary' => $this->buildSummary($dokumenta),
            'completion' => $this->getKandidatDocumentCompletion($kandidat),
        ];
    }

    public function kandidatHasCompleteRequiredDocuments(Kandidat|int $kandidat): bool
    {
        return $this->getKandidatDocumentCompletion($kandidat)['is_complete'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getKandidatDocumentCompletion(Kandidat|int $kandidat): array
    {
        $resolvedKandidat = $kandidat instanceof Kandidat
            ? $kandidat->loadMissing('program', 'tipStudija', 'godinaStudija', 'kandidatDokumenta.dokument', 'kandidatDokumenta.reviewer')
            : Kandidat::query()->with(['program', 'tipStudija', 'godinaStudija', 'kandidatDokumenta.dokument', 'kandidatDokumenta.reviewer'])->findOrFail($kandidat);

        $requiredDocuments = $this->getRequiredDocumentsForKandidat($resolvedKandidat);
        $requiredDocumentIds = $requiredDocuments->pluck('id')->all();
        $requiredAttachments = $resolvedKandidat->kandidatDokumenta
            ->filter(fn (KandidatPrilozenaDokumenta $attachment) => in_array($attachment->prilozenaDokumenta_id, $requiredDocumentIds, true))
            ->values();
        $approvedRequiredAttachments = $requiredAttachments
            ->where('review_status', KandidatPrilozenaDokumenta::STATUS_APPROVED)
            ->values();
        $missingDocuments = $requiredDocuments
            ->filter(fn (PrilozenaDokumenta $document) => ! $requiredAttachments->contains('prilozenaDokumenta_id', $document->id))
            ->values();
        $reviewBlockedAttachments = $requiredAttachments
            ->filter(fn (KandidatPrilozenaDokumenta $attachment) => $attachment->review_status !== KandidatPrilozenaDokumenta::STATUS_APPROVED)
            ->values();
        $requiredCount = $requiredDocuments->count();
        $approvedCount = $approvedRequiredAttachments->count();
        $completionPercentage = $requiredCount === 0
            ? 100
            : (int) round(($approvedCount / $requiredCount) * 100);

        return [
            'required_documents' => $requiredDocuments,
            'required_count' => $requiredCount,
            'attached_required_count' => $requiredAttachments->count(),
            'approved_required_count' => $approvedCount,
            'missing_documents' => $missingDocuments,
            'missing_count' => $missingDocuments->count(),
            'review_blocked_attachments' => $reviewBlockedAttachments,
            'review_blocked_count' => $reviewBlockedAttachments->count(),
            'completion_percentage' => $completionPercentage,
            'is_complete' => $missingDocuments->isEmpty() && $reviewBlockedAttachments->isEmpty(),
        ];
    }

    public function approveDocument(Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment, User $reviewer, ?string $notes = null): KandidatPrilozenaDokumenta
    {
        return $this->transitionReviewStatus($kandidat, $attachment, KandidatPrilozenaDokumenta::STATUS_APPROVED, $reviewer, $notes);
    }

    public function rejectDocument(Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment, User $reviewer, string $notes): KandidatPrilozenaDokumenta
    {
        return $this->transitionReviewStatus($kandidat, $attachment, KandidatPrilozenaDokumenta::STATUS_REJECTED, $reviewer, $notes);
    }

    public function requestDocumentRevision(Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment, User $reviewer, string $notes): KandidatPrilozenaDokumenta
    {
        return $this->transitionReviewStatus($kandidat, $attachment, KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION, $reviewer, $notes);
    }

    private function transitionReviewStatus(
        Kandidat $kandidat,
        KandidatPrilozenaDokumenta $attachment,
        string $status,
        User $reviewer,
        ?string $notes = null
    ): KandidatPrilozenaDokumenta {
        if ($attachment->kandidat_id !== $kandidat->id) {
            throw (new ModelNotFoundException())->setModel(KandidatPrilozenaDokumenta::class, [$attachment->id]);
        }

        DB::transaction(function () use ($attachment, $status, $reviewer, $notes): void {
            $attachment->review_status = $status;
            $attachment->reviewer_id = $reviewer->id;
            $attachment->notes = $this->normalizeNotes($notes);
            $attachment->reviewed_at = Carbon::now();
            $attachment->save();
        });

        $refreshedAttachment = $attachment->fresh(['dokument', 'reviewer']);

        $this->dispatchStatusNotifications($kandidat->fresh(['kandidatDokumenta.dokument']), $refreshedAttachment, $status);

        return $refreshedAttachment;
    }

    private function buildSummary(Collection $dokumenta): array
    {
        return [
            'total' => $dokumenta->count(),
            'pending' => $dokumenta->where('review_status', KandidatPrilozenaDokumenta::STATUS_PENDING)->count(),
            'approved' => $dokumenta->where('review_status', KandidatPrilozenaDokumenta::STATUS_APPROVED)->count(),
            'rejected' => $dokumenta->where('review_status', KandidatPrilozenaDokumenta::STATUS_REJECTED)->count(),
            'needs_revision' => $dokumenta->where('review_status', KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION)->count(),
        ];
    }

    private function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $trimmed = trim($notes);

        return $trimmed === '' ? null : $trimmed;
    }

    private function buildCandidateReviewRow(Kandidat $kandidat): array
    {
        $completion = $this->getKandidatDocumentCompletion($kandidat);

        return [
            'kandidat' => $kandidat,
            'completion' => $completion,
            'is_complete' => $completion['is_complete'],
        ];
    }

    private function getRequiredDocumentsForKandidat(Kandidat $kandidat): Collection
    {
        $groupId = $this->resolveRequiredDocumentGroupId($kandidat);

        if ($groupId === null) {
            return collect();
        }

        return PrilozenaDokumenta::query()
            ->where('skolskaGodina_id', (string) $groupId)
            ->orderBy('redniBrojDokumenta')
            ->get();
    }

    private function resolveRequiredDocumentGroupId(Kandidat $kandidat): ?int
    {
        $skrNaziv = $kandidat->tipStudija?->skrNaziv;
        $isFirstYear = $kandidat->godinaStudija?->redosledPrikazivanja === 1
            || $kandidat->godinaStudija?->nazivRimski === 'I'
            || $kandidat->godinaStudija_id === 1;

        return match ($skrNaziv) {
            'OAS' => $isFirstYear ? 1 : 2,
            'MAS' => 3,
            default => null,
        };
    }

    private function dispatchStatusNotifications(Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment, string $status): void
    {
        $user = $this->resolveCandidateUser($kandidat);

        if ($user === null) {
            return;
        }

        $documentName = $attachment->dokument?->naziv ?? 'Документ';

        if ($status === KandidatPrilozenaDokumenta::STATUS_REJECTED) {
            $this->notificationService->notifyUser(
                $user->id,
                'Документ је одбијен',
                "Документ '{$documentName}' је одбијен. Разлог: ".($attachment->notes ?? 'Није наведен.'),
                'error',
                ['kandidat_id' => $kandidat->id, 'attachment_id' => $attachment->id]
            );

            return;
        }

        if ($status === KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION) {
            $this->notificationService->notifyUser(
                $user->id,
                'Потребна је допуна документације',
                "За документ '{$documentName}' је затражена допуна. Напомена: ".($attachment->notes ?? 'Није наведено.'),
                'warning',
                ['kandidat_id' => $kandidat->id, 'attachment_id' => $attachment->id]
            );

            return;
        }

        if ($status === KandidatPrilozenaDokumenta::STATUS_APPROVED && $this->kandidatHasCompleteRequiredDocuments($kandidat)) {
            $this->notificationService->notifyUser(
                $user->id,
                'Документација је комплетна',
                'Сва обавезна документа су одобрена. Можете наставити поступак уписа.',
                'success',
                ['kandidat_id' => $kandidat->id]
            );
        }
    }

    private function resolveCandidateUser(Kandidat $kandidat): ?User
    {
        if (empty($kandidat->email)) {
            return null;
        }

        return User::query()
            ->where('email', $kandidat->email)
            ->first();
    }
}