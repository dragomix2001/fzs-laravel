<?php

namespace App\DTOs;

use App\DTOs\Concerns\NormalizesRequestValues;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class KandidatPage1Data
{
    use NormalizesRequestValues;

    public function __construct(
        public string $ime,
        public string $prezime,
        public string $JMBG,
        public bool $uplata,
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
        public int $studijskiProgramId,
        public ?int $skolskaGodinaUpisaId,
        public ?string $drzavaZavrseneSkole,
        public ?string $godinaZavrsetkaSkole,
        public ?string $drzavaRodjenja,
        public ?int $godinaStudijaId,
        public ?UploadedFile $imageUpload,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ime: (string) $request->input('ImeKandidata'),
            prezime: (string) $request->input('PrezimeKandidata'),
            JMBG: (string) $request->input('JMBG'),
            uplata: $request->boolean('uplata'),
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
            studijskiProgramId: (int) $request->input('StudijskiProgram'),
            skolskaGodinaUpisaId: self::nullableInt($request->input('SkolskeGodineUpisa')),
            drzavaZavrseneSkole: self::nullableString($request->input('drzavaZavrseneSkole')),
            godinaZavrsetkaSkole: self::nullableString($request->input('godinaZavrsetkaSkole')),
            drzavaRodjenja: self::nullableString($request->input('drzavaRodjenja')),
            godinaStudijaId: self::nullableInt($request->input('GodinaStudija')),
            imageUpload: $request->file('imageUpload'),
        );
    }
}