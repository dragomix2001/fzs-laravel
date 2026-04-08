<?php

namespace App\DTOs;

use App\DTOs\Concerns\NormalizesRequestValues;
use Illuminate\Http\Request;

readonly class KandidatPage2Data
{
    use NormalizesRequestValues;

    public function __construct(
        public int $kandidatId,
        public array $grades,
        public ?int $opstiUspehSrednjaSkolaId,
        public ?float $srednjaOcenaSrednjaSkola,
        public array $sports,
        public ?float $visina,
        public ?float $telesnaTezina,
        public array $dokumentiPrva,
        public array $dokumentiDruga,
        public ?float $brojBodovaTest,
        public ?float $brojBodovaSkola,
        public ?float $ukupniBrojBodova,
        public ?string $upisniRok,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            kandidatId: (int) $request->input('insertedId'),
            grades: self::buildGrades($request),
            opstiUspehSrednjaSkolaId: self::nullableInt($request->input('OpstiUspehSrednjaSkola')),
            srednjaOcenaSrednjaSkola: self::nullableFloat($request->input('SrednjaOcenaSrednjaSkola')),
            sports: self::buildSports($request),
            visina: self::nullableFloat($request->input('VisinaKandidata')),
            telesnaTezina: self::nullableFloat($request->input('TelesnaTezinaKandidata')),
            dokumentiPrva: self::normalizeArray($request->input('dokumentiPrva', [])),
            dokumentiDruga: self::normalizeArray($request->input('dokumentiDruga', [])),
            brojBodovaTest: self::nullableFloat($request->input('BrojBodovaTest')),
            brojBodovaSkola: self::nullableFloat($request->input('BrojBodovaSkola')),
            ukupniBrojBodova: self::nullableFloat($request->input('ukupniBrojBodova')),
            upisniRok: self::nullableString($request->input('UpisniRok')),
        );
    }

    private static function buildGrades(Request $request): array
    {
        return [
            ['razred' => 1, 'uspeh' => self::nullableInt($request->input('prviRazred')), 'ocena' => self::nullableFloat($request->input('SrednjaOcena1'))],
            ['razred' => 2, 'uspeh' => self::nullableInt($request->input('drugiRazred')), 'ocena' => self::nullableFloat($request->input('SrednjaOcena2'))],
            ['razred' => 3, 'uspeh' => self::nullableInt($request->input('treciRazred')), 'ocena' => self::nullableFloat($request->input('SrednjaOcena3'))],
            ['razred' => 4, 'uspeh' => self::nullableInt($request->input('cetvrtiRazred')), 'ocena' => self::nullableFloat($request->input('SrednjaOcena4'))],
        ];
    }

    private static function buildSports(Request $request): array
    {
        $sports = [];

        foreach ([1, 2, 3] as $index) {
            $sportId = self::nullableInt($request->input("sport{$index}"));
            if ($sportId === null || $sportId === 0) {
                continue;
            }

            $sports[] = [
                'sport' => $sportId,
                'klub' => self::nullableString($request->input("klub{$index}")),
                'uzrast' => self::nullableString($request->input("uzrast{$index}")),
                'godine' => self::nullableString($request->input("godine{$index}")),
            ];
        }

        return $sports;
    }
}
