<?php

namespace App\DTOs;

use App\DTOs\Concerns\NormalizesRequestValues;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class KandidatUpdateData
{
    use NormalizesRequestValues;

    public function __construct(
        public string $ime,
        public string $prezime,
        public string $JMBG,
        public bool $uplata,
        public ?UploadedFile $imageUpload,
        public ?UploadedFile $pdfUpload,
        public ?\DateTimeInterface $datumRodjenja,
        public ?string $mestoRodjenja,
        public ?int $krsnaSlavaId,
        public ?string $kontaktTelefon,
        public ?string $adresaStanovanja,
        public ?string $email,
        public ?string $imePrezimeJednogRoditelja,
        public ?string $kontaktTelefonRoditelja,
        public ?string $srednjeSkoleFakulteti,
        public ?string $mestoZavrseneSkoleFakulteta,
        public ?string $smerZavrseneSkoleFakulteta,
        public int $tipStudijaId,
        public int $studijskiProgramId,
        public ?int $skolskaGodinaUpisaId,
        public ?int $godinaStudijaId,
        public ?string $drzavaZavrseneSkole,
        public ?string $godinaZavrsetkaSkole,
        public ?string $drzavaRodjenja,
        public ?int $statusUpisaId,
        public ?\DateTimeInterface $datumStatusa,
        public array $grades,
        public ?int $opstiUspehSrednjaSkolaId,
        public ?float $srednjaOcenaSrednjaSkola,
        public ?float $visina,
        public ?float $telesnaTezina,
        public array $dokumentiPrva,
        public array $dokumentiDruga,
        public ?float $brojBodovaTest,
        public ?float $brojBodovaSkola,
        public ?float $ukupniBrojBodova,
        public ?string $upisniRok,
        public ?int $indikatorAktivan,
        public ?string $brojIndeksa,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ime: (string) $request->input('ImeKandidata'),
            prezime: (string) $request->input('PrezimeKandidata'),
            JMBG: (string) $request->input('JMBG'),
            uplata: $request->boolean('uplata'),
            imageUpload: $request->file('imageUpload'),
            pdfUpload: $request->file('pdfUpload'),
            datumRodjenja: self::nullableDate($request->input('DatumRodjenja')),
            mestoRodjenja: self::nullableString($request->input('mestoRodjenja')),
            krsnaSlavaId: self::nullableInt($request->input('KrsnaSlava')),
            kontaktTelefon: self::nullableString($request->input('KontaktTelefon')),
            adresaStanovanja: self::nullableString($request->input('AdresaStanovanja')),
            email: self::nullableString($request->input('Email')),
            imePrezimeJednogRoditelja: self::nullableString($request->input('ImePrezimeJednogRoditelja')),
            kontaktTelefonRoditelja: self::nullableString($request->input('KontaktTelefonRoditelja')),
            srednjeSkoleFakulteti: self::nullableString($request->input('NazivSkoleFakulteta')),
            mestoZavrseneSkoleFakulteta: self::nullableString($request->input('mestoZavrseneSkoleFakulteta')),
            smerZavrseneSkoleFakulteta: self::nullableString($request->input('SmerZavrseneSkoleFakulteta')),
            tipStudijaId: (int) $request->input('TipStudija', 1),
            studijskiProgramId: (int) $request->input('StudijskiProgram'),
            skolskaGodinaUpisaId: self::nullableInt($request->input('SkolskeGodineUpisa')),
            godinaStudijaId: self::nullableInt($request->input('GodinaStudija')),
            drzavaZavrseneSkole: self::nullableString($request->input('drzavaZavrseneSkole')),
            godinaZavrsetkaSkole: self::nullableString($request->input('godinaZavrsetkaSkole')),
            drzavaRodjenja: self::nullableString($request->input('drzavaRodjenja')),
            statusUpisaId: self::nullableInt($request->input('statusUpisa_id')),
            datumStatusa: self::nullableDate($request->input('datumStatusa')),
            grades: self::buildGrades($request),
            opstiUspehSrednjaSkolaId: self::nullableInt($request->input('OpstiUspehSrednjaSkola')),
            srednjaOcenaSrednjaSkola: self::nullableFloat($request->input('SrednjaOcenaSrednjaSkola')),
            visina: self::nullableFloat($request->input('VisinaKandidata')),
            telesnaTezina: self::nullableFloat($request->input('TelesnaTezinaKandidata')),
            dokumentiPrva: self::normalizeArray($request->input('dokumentiPrva', [])),
            dokumentiDruga: self::normalizeArray($request->input('dokumentiDruga', [])),
            brojBodovaTest: self::nullableFloat($request->input('BrojBodovaTest')),
            brojBodovaSkola: self::nullableFloat($request->input('BrojBodovaSkola')),
            ukupniBrojBodova: self::nullableFloat($request->input('ukupniBrojBodova')),
            upisniRok: self::nullableString($request->input('UpisniRok')),
            indikatorAktivan: self::nullableInt($request->input('IndikatorAktivan')),
            brojIndeksa: self::nullableString($request->input('BrojIndeksa') ?: $request->input('brojIndeksa')),
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
}
