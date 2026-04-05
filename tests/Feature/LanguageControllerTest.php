<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class LanguageControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        Mockery::close();
        parent::tearDown();
    }

    // ============================================================
    // SWITCH METHOD TESTS
    // ============================================================

    public function test_switch_sets_locale_to_sr_when_lang_parameter_is_sr(): void
    {
        $response = $this->followingRedirects()->get('/language/switch?lang=sr');

        $response->assertSessionHas('locale', 'sr');
        $this->assertEquals('sr', app()->getLocale());
    }

    public function test_switch_sets_locale_to_en_when_lang_parameter_is_en(): void
    {
        $response = $this->followingRedirects()->get('/language/switch?lang=en');

        $response->assertSessionHas('locale', 'en');
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_switch_defaults_to_sr_when_lang_parameter_is_invalid(): void
    {
        $response = $this->followingRedirects()->get('/language/switch?lang=invalid');

        $response->assertSessionHas('locale', 'sr');
        $this->assertEquals('sr', app()->getLocale());
    }

    public function test_switch_defaults_to_sr_when_lang_parameter_is_missing(): void
    {
        $response = $this->followingRedirects()->get('/language/switch');

        $response->assertSessionHas('locale', 'sr');
        $this->assertEquals('sr', app()->getLocale());
    }

    public function test_switch_stores_locale_in_session(): void
    {
        $response = $this->followingRedirects()->get('/language/switch?lang=en');

        $response->assertSessionHas('locale', 'en');
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_switch_redirects_back_to_previous_page(): void
    {
        $response = $this->followingRedirects()->get('/language/switch?lang=sr', [
            'HTTP_REFERER' => 'http://localhost/dashboard',
        ]);

        $response->assertStatus(200);
        $response->assertSessionHas('locale', 'sr');
        $this->assertEquals('sr', app()->getLocale());
    }
}
