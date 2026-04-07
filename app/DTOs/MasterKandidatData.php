<?php

namespace App\DTOs;

use App\DTOs\Concerns\NormalizesRequestValues;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class MasterKandidatData
{
    use NormalizesRequestValues;

    public function __construct(
        public string $ime,
        public string $prezime,
        public string $JMBG,
        public bool $uplata,
        public ?int $statusUpisaId,
        public ?\DateTimeInterface $datumStatusa,
        public ?UploadedFile $imageUpload,
        public ?string $mestoRodjenja,
        public ?string $kontaktTelefon,
        public ?string $adresaStanovanja,
        public ?string $email,
        public ?string $srednjeSkoleFakulteti,
        public ?string $mestoZavrseneSkoleFakulteta,
        public ?string $smerZavrseneSkoleFakulteta,
        public int $tipStudijaId,
        public int $studijskiProgramId,
        public ?int $skolskaGodinaUpisaId,
        public ?float $prosecnaOcena,
        public ?string $upisniRok,
        public int $godinaStudijaId,
        public ?string $brojIndeksa,
        public ?string $drzavaZavrseneSkole,
        public ?string $godinaZavrsetkaSkole,
        public ?string $drzavaRodjenja,
        public array $dokumentaMaster,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ime: (string) $request->input('ImeKandidata'),
            prezime: (string) $request->input('PrezimeKandidata'),
            JMBG: (string) $request->input('JMBG'),
            uplata: $request->boolean('uplata'),
            statusUpisaId: self::nullableInt($request->input('statusUpisa_id')),
            datumStatusa: self::nullableDate($request->input('datumStatusa')),
            imageUpload: $request->file('imageUpload'),
            mestoRodjenja: self::nullableString($request->input('mestoRodjenja')),
            kontaktTelefon: self::nullableString($request->input('KontaktTelefon')),
            adresaStanovanja: self::nullableString($request->input('AdresaStanovanja')),
            email: self::nullableString($request->input('Email')),
            srednjeSkoleFakulteti: self::nullableString($request->input('NazivSkoleFakulteta')),
            mestoZavrseneSkoleFakulteta: self::nullableString($request->input('mestoZavrseneSkoleFakulteta')),
            smerZavrseneSkoleFakulteta: self::nullableString($request->input('SmerZavrseneSkoleFakulteta')),
            tipStudijaId: (int) $request->input('TipStudija', 2),
            studijskiProgramId: (int) $request->input('StudijskiProgram'),
            skolskaGodinaUpisaId: self::nullableInt($request->input('SkolskeGodineUpisa')),
            prosecnaOcena: self::nullableFloat($request->input('ProsecnaOcena')),
            upisniRok: self::nullableString($request->input('UpisniRok')),
            godinaStudijaId: self::nullableInt($request->input('GodinaStudija')) ?? 1,
            brojIndeksa: self::nullableString($request->input('brojIndeksa')),
            drzavaZavrseneSkole: self::nullableString($request->input('drzavaZavrseneSkole')),
            godinaZavrsetkaSkole: self::nullableString($request->input('godinaZavrsetkaSkole')),
            drzavaRodjenja: self::nullableString($request->input('drzavaRodjenja')),
            dokumentaMaster: self::normalizeArray($request->input('dokumentaMaster', [])),
        );
    }
}