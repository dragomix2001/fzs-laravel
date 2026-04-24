<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DocumentReviewRequest;
use App\Models\Kandidat;
use App\Models\KandidatPrilozenaDokumenta;
use App\Models\User;
use App\Services\DocumentReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentReviewController extends Controller
{
    public function __construct(private readonly DocumentReviewService $documentReviewService)
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(): View
    {
        return view('kandidat.documents_incomplete', [
            'rows' => $this->documentReviewService->getIncompleteCandidatesReviewList(),
        ]);
    }

    public function show(Kandidat $kandidat): View
    {
        return view('kandidat.documents_review', $this->documentReviewService->getReviewPageData($kandidat));
    }

    public function approve(DocumentReviewRequest $request, Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment): RedirectResponse
    {
        $this->documentReviewService->approveDocument($kandidat, $attachment, $this->resolveReviewer($request->user()), $request->validated('notes'));

        return redirect()
            ->route('kandidat.documents.review', $kandidat)
            ->with('success', 'Документ је одобрен.');
    }

    public function reject(DocumentReviewRequest $request, Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment): RedirectResponse
    {
        $this->documentReviewService->rejectDocument($kandidat, $attachment, $this->resolveReviewer($request->user()), (string) $request->validated('notes'));

        return redirect()
            ->route('kandidat.documents.review', $kandidat)
            ->with('success', 'Документ је одбијен.');
    }

    public function needsRevision(DocumentReviewRequest $request, Kandidat $kandidat, KandidatPrilozenaDokumenta $attachment): RedirectResponse
    {
        $this->documentReviewService->requestDocumentRevision($kandidat, $attachment, $this->resolveReviewer($request->user()), (string) $request->validated('notes'));

        return redirect()
            ->route('kandidat.documents.review', $kandidat)
            ->with('success', 'За документ је затражена допуна.');
    }

    private function resolveReviewer(mixed $reviewer): User
    {
        if (! $reviewer instanceof User) {
            abort(403);
        }

        return $reviewer;
    }
}