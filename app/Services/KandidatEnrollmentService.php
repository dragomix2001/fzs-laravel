<?php

namespace App\Services;

use App\Jobs\MassEnrollmentJob;
use App\Models\Kandidat;
use App\Models\UpisGodine;
use Carbon\Carbon;

/**
 * Enrollment and mass operation service for student candidates.
 *
 * Extracted from KandidatService to separate batch enrollment/payment
 * workflows from individual CRUD operations.
 * See docs/ADR/001-god-services.md
 */
class KandidatEnrollmentService
{
    public function __construct(private UpisService $upisService) {}

    /** Masovna uplata za osnovne studije. */
    public function masovnaUplata(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();

            UpisGodine::uplatiGodinu($kandidatId, 1);
        }
    }

    /** Masovni upis za osnovne studije. */
    public function masovniUpis(array $kandidatIds): bool
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $this->upisService->registrujKandidata($kandidatId);

            $returnValue = $this->upisService->upisiGodinu($kandidatId, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);

            if ($returnValue) {
                $kandidat->statusUpisa_id = 1;
                $kandidat->datumStatusa = Carbon::now();
                $kandidat->save();
            } else {
                return false;
            }
        }

        return true;
    }

    /** Masovna uplata za master studije. */
    public function masovnaUplataMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();
        }
    }

    /** Masovni upis za master studije. */
    public function masovniUpisMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->statusUpisa_id = 1;
            $kandidat->datumStatusa = Carbon::now();
            $kandidat->save();

            $this->upisService->generisiBrojIndeksa($kandidatId);
        }
    }

    public function masovniUpisAsync(array $kandidatIds): array
    {
        MassEnrollmentJob::dispatch($kandidatIds);

        return ['status' => 'queued', 'count' => count($kandidatIds)];
    }

    /** Upis kandidata (enrollment logic). */
    public function upisKandidata(int $id): array
    {
        $kandidat = Kandidat::find($id);
        $this->upisService->registrujKandidata($id);

        if ($kandidat->tipStudija_id == 1) {
            $check = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $check) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
        } elseif ($kandidat->tipStudija_id == 2) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        } elseif ($kandidat->tipStudija_id == 3) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        }

        $kandidat->statusUpisa_id = 1;
        $kandidat->datumStatusa = Carbon::now();
        $saved = $kandidat->save();

        return [
            'success' => $saved,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ];
    }

    /** Registracija kandidata. */
    public function registracijaKandidata(int $id): void
    {
        $this->upisService->registrujKandidata($id);
    }
}
