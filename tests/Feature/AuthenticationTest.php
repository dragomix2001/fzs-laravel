<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Kandidat;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_guest_can_see_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Пријава');
    }

    /** @test */
    public function test_authenticated_user_cannot_see_login_page()
    {
        $user = Kandidat::create([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'User',
            'jmbg' => '1234567890123',
            'datumRodjenja' => '2000-01-01',
            'mestoRodjenja' => 'Test',
            'krsnaSlava_id' => 1,
            'kontaktTelefon' => '123456',
            'adresaStanovanja' => 'Test adresa',
            'email' => 'test@example.com',
            'imePrezimeJednogRoditelja' => 'Test Roditelj',
            'kontaktTelefonRoditelja' => '123456',
            'srednjeSkoleFakulteti' => 'Test',
            'mestoZavrseneSkoleFakulteta' => 'Test',
            'smerZavrseneSkoleFakulteta' => 'Test',
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'srednjaOcenaSrednjaSkola' => 4.0,
            'sportskoAngazovanje_id' => 1,
            'telesnaTezina' => 70.0,
            'visina' => 180,
            'prilozenaDokumentaPrvaGodina_id' => 1,
            'statusUpisa_id' => 1,
            'brojBodovaTest' => 80,
            'brojBodovaSkola' => 80,
            'ukupniBrojBodova' => 160,
            'prosecnaOcena' => 4.0,
            'upisniRok' => '2024/2025',
            'brojIndeksa' => 'TEST123',
            'skolskaGodinaUpisa_id' => 1,
            'indikatorAktivan' => 1,
            'studijskiProgram_id' => 1,
            'tipStudija_id' => 1,
            'godinaStudija_id' => 1,
            'mesto_id' => 1,
            'uplata' => 0,
            'upisan' => 1,
            'drzavaZavrseneSkole' => 'Srbija',
            'drzavaRodjenja' => 'Srbija',
            'godinaZavrsetkaSkole' => 2018,
            'slika' => null,
            'diplomski' => 0,
            'datumStatusa' => null,
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/home');
    }

    /** @test */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = Kandidat::create([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'User',
            'jmbg' => '1234567890123',
            'datumRodjenja' => '2000-01-01',
            'mestoRodjenja' => 'Test',
            'krsnaSlava_id' => 1,
            'kontaktTelefon' => '123456',
            'adresaStanovanja' => 'Test adresa',
            'email' => 'test@example.com',
            'imePrezimeJednogRoditelja' => 'Test Roditelj',
            'kontaktTelefonRoditelja' => '123456',
            'srednjeSkoleFakulteti' => 'Test',
            'mestoZavrseneSkoleFakulteta' => 'Test',
            'smerZavrseneSkoleFakulteta' => 'Test',
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'srednjaOcenaSrednjaSkola' => 4.0,
            'sportskoAngazovanje_id' => 1,
            'telesnaTezina' => 70.0,
            'visina' => 180,
            'prilozenaDokumentaPrvaGodina_id' => 1,
            'statusUpisa_id' => 1,
            'brojBodovaTest' => 80,
            'brojBodovaSkola' => 80,
            'ukupniBrojBodova' => 160,
            'prosecnaOcena' => 4.0,
            'upisniRok' => '2024/2025',
            'brojIndeksa' => 'TEST123',
            'skolskaGodinaUpisa_id' => 1,
            'indikatorAktivan' => 1,
            'studijskiProgram_id' => 1,
            'tipStudija_id' => 1,
            'godinaStudija_id' => 1,
            'mesto_id' => 1,
            'uplata' => 0,
            'upisan' => 1,
            'drzavaZavrseneSkole' => 'Srbija',
            'drzavaRodjenja' => 'Srbija',
            'godinaZavrsetkaSkole' => 2018,
            'slika' => null,
            'diplomski' => 0,
            'datumStatusa' => null,
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = Kandidat::create([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'User',
            'jmbg' => '1234567890123',
            'datumRodjenja' => '2000-01-01',
            'mestoRodjenja' => 'Test',
            'krsnaSlava_id' => 1,
            'kontaktTelefon' => '123456',
            'adresaStanovanja' => 'Test adresa',
            'email' => 'test@example.com',
            'imePrezimeJednogRoditelja' => 'Test Roditelj',
            'kontaktTelefonRoditelja' => '123456',
            'srednjeSkoleFakulteti' => 'Test',
            'mestoZavrseneSkoleFakulteta' => 'Test',
            'smerZavrseneSkoleFakulteta' => 'Test',
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'srednjaOcenaSrednjaSkola' => 4.0,
            'sportskoAngazovanje_id' => 1,
            'telesnaTezina' => 70.0,
            'visina' => 180,
            'prilozenaDokumentaPrvaGodina_id' => 1,
            'statusUpisa_id' => 1,
            'brojBodovaTest' => 80,
            'brojBodovaSkola' => 80,
            'ukupniBrojBodova' => 160,
            'prosecnaOcena' => 4.0,
            'upisniRok' => '2024/2025',
            'brojIndeksa' => 'TEST123',
            'skolskaGodinaUpisa_id' => 1,
            'indikatorAktivan' => 1,
            'studijskiProgram_id' => 1,
            'tipStudija_id' => 1,
            'godinaStudija_id' => 1,
            'mesto_id' => 1,
            'uplata' => 0,
            'upisan' => 1,
            'drzavaZavrseneSkole' => 'Srbija',
            'drzavaRodjenja' => 'Srbija',
            'godinaZavrsetkaSkole' => 2018,
            'slika' => null,
            'diplomski' => 0,
            'datumStatusa' => null,
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function test_authenticated_user_can_access_dashboard()
    {
        $user = Kandidat::create([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'User',
            'jmbg' => '1234567890123',
            'datumRodjenja' => '2000-01-01',
            'mestoRodjenja' => 'Test',
            'krsnaSlava_id' => 1,
            'kontaktTelefon' => '123456',
            'adresaStanovanja' => 'Test adresa',
            'email' => 'test@example.com',
            'imePrezimeJednogRoditelja' => 'Test Roditelj',
            'kontaktTelefonRoditelja' => '123456',
            'srednjeSkoleFakulteti' => 'Test',
            'mestoZavrseneSkoleFakulteta' => 'Test',
            'smerZavrseneSkoleFakulteta' => 'Test',
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'srednjaOcenaSrednjaSkola' => 4.0,
            'sportskoAngazovanje_id' => 1,
            'telesnaTezina' => 70.0,
            'visina' => 180,
            'prilozenaDokumentaPrvaGodina_id' => 1,
            'statusUpisa_id' => 1,
            'brojBodovaTest' => 80,
            'brojBodovaSkola' => 80,
            'ukupniBrojBodova' => 160,
            'prosecnaOcena' => 4.0,
            'upisniRok' => '2024/2025',
            'brojIndeksa' => 'TEST123',
            'skolskaGodinaUpisa_id' => 1,
            'indikatorAktivan' => 1,
            'studijskiProgram_id' => 1,
            'tipStudija_id' => 1,
            'godinaStudija_id' = 1,
            'mesto_id' = 1,
            'uplata' = 0,
            'upisan' = 1,
            'drzavaZavrseneSkole' = 'Srbija',
            'drzavaRodjenja' = 'Srbija',
            'godinaZavrsetkaSkole' = 2018,
            'slika' = null,
            'diplomski' = 0,
            'datumStatusa' = null,
            'email' = 'test@example.com',
            'password' = Hash::make('password')
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_can_logout()
    {
        $user = Kandidat::create([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'User',
            'jmbg' => '1234567890123',
            'datumRodjenja' = '2000-01-01',
            'mestoRodjenja' = 'Test',
            'krsnaSlava_id' = 1,
            'kontaktTelefon' = '123456',
            'adresaStanovanja' = 'Test adresa',
            'email' = 'test@example.com',
            'imePrezimeJednogRoditelja' = 'Test Roditelj',
            'kontaktTelefonRoditelja' = '123456',
            'srednjeSkoleFakulteti' = 'Test',
            'mestoZavrseneSkoleFakulteta' = 'Test',
            'smerZavrseneSkoleFakulteta' = 'Test',
            'uspehSrednjaSkola_id' = 1,
            'opstiUspehSrednjaSkola_id' = 1,
            'srednjaOcenaSrednjaSkola' = 4.0,
            'sportskoAngazovanje_id' = 1,
            'telesnaTezina' = 70.0,
            'visina' = 180,
            'prilozenaDokumentaPrvaGodina_id' = 1,
            'statusUpisa_id' = 1,
            'brojBodovaTest' = 80,
            'brojBodovaSkola' = 80,
            'ukupniBrojBodova' = 160,
            'prosecnaOcena' = 4.0,
            'upisniRok' = '2024/2025',
            'brojIndeksa' = 'TEST123',
            'skolskaGodinaUpisa_id' = 1,
            'indikatorAktivan' = 1,
            'studijskiProgram_id' = 1,
            'tipStudija_id' = 1,
            'godinaStudija_id' = 1,
            'mesto_id' = 1,
            'uplata' = 0,
            'upisan' = 1,
            'drzavaZavrseneSkole' = 'Srbija',
            'drzavaRodjenja' = 'Srbija',
            'godinaZavrsetkaSkole' = 2018,
            'slika' = null,
            'diplomski' = 0,
            'datumStatusa' = null,
            'email' = 'test@example.com',
            'password' = Hash::make('password')
        ]);

        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect('/');

        $this->assertGuest();
    }
}