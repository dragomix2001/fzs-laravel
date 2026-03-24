<?php

namespace App\Services;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\KrsnaSlava;
use App\Models\Opstina;
use App\Models\OpstiUspeh;
use App\Models\PrilozenaDokumenta;
use App\Models\SkolskaGodUpisa;
use App\Models\SportskoAngazovanje;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UspehSrednjaSkola;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KandidatService
{
    /**
     * Get all kandidati with filters
     */
    public function getAll(array $filters = [])
    {
        $query = Kandidat::query();

        if (! empty($filters['tipStudija_id'])) {
            $query->where('tipStudija_id', $filters['tipStudija_id']);
        }

        if (! empty($filters['statusUpisa_id'])) {
            $query->where('statusUpisa_id', $filters['statusUpisa_id']);
        }

        if (! empty($filters['studijskiProgram_id'])) {
            $query->where('studijskiProgram_id', $filters['studijskiProgram_id']);
        }

        return $query->get();
    }

    /**
     * Get kandidat by ID
     */
    public function findById(int $id): ?Kandidat
    {
        return Kandidat::find($id);
    }

    /**
     * Get active studijski program for osnovne studije
     */
    public function getActiveStudijskiProgramOsnovne(): int
    {
        return Cache::remember('active_studijski_program_osnovne', 3600, function () {
            return StudijskiProgram::where(['tipStudija_id' => 1, 'indikatorAktivan' => 1])->value('id');
        });
    }

    /**
     * Get studijski programi for tip studija
     */
    public function getStudijskiProgrami(int $tipStudijaId): mixed
    {
        $cacheKey = "studijski_programi_tip_{$tipStudijaId}";

        return Cache::remember($cacheKey, 3600, function () use ($tipStudijaId) {
            return StudijskiProgram::where('tipStudija_id', $tipStudijaId)->get();
        });
    }

    /**
     * Get all dropdown data for kandidat create form
     */
    public function getDropdownData(): array
    {
        return Cache::remember('kandidat_dropdown_data', 3600, function () {
            return [
                'mestoRodjenja' => Opstina::all(),
                'krsnaSlava' => KrsnaSlava::all(),
                'opstiUspehSrednjaSkola' => OpstiUspeh::all(),
                'uspehSrednjaSkola' => UspehSrednjaSkola::all(),
                'sportskoAngazovanje' => SportskoAngazovanje::all(),
                'prilozeniDokumentPrvaGodina' => PrilozenaDokumenta::all(),
                'statusaUpisaKandidata' => StatusStudiranja::all(),
                'studijskiProgram' => StudijskiProgram::where('tipStudija_id', '1')->get(),
                'tipStudija' => TipStudija::all(),
                'godinaStudija' => GodinaStudija::all(),
                'skolskeGodineUpisa' => SkolskaGodUpisa::all(),
            ];
        });
    }

    /**
     * Create new kandidat
     */
    public function create(array $data): Kandidat
    {
        return DB::transaction(function () use ($data) {
            $kandidat = Kandidat::create($data);

            return $kandidat;
        });
    }

    /**
     * Update kandidat
     */
    public function update(int $id, array $data): ?Kandidat
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return null;
        }

        $kandidat->update($data);

        return $kandidat;
    }

    /**
     * Delete kandidat
     */
    public function delete(int $id): bool
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return false;
        }

        return $kandidat->delete();
    }

    /**
     * Get kandidati by status
     */
    public function getByStatus(int $statusId): mixed
    {
        return Kandidat::where('statusUpisa_id', $statusId)->get();
    }

    /**
     * Get kandidati by studijski program
     */
    public function getByStudijskiProgram(int $programId): mixed
    {
        return Kandidat::where('studijskiProgram_id', $programId)->get();
    }

    /**
     * Search kandidati
     */
    public function search(string $query): mixed
    {
        return Kandidat::where('imeKandidata', 'like', "%{$query}%")
            ->orWhere('prezimeKandidata', 'like', "%{$query}%")
            ->orWhere('brojIndeksa', 'like', "%{$query}%")
            ->get();
    }
}
