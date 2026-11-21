<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Skolarina;
use App\Models\UpisGodine;
use App\Models\UplataSkolarine;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class MassAssignmentProtectionTest extends TestCase
{
    private bool $modelWasUnguarded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelWasUnguarded = Model::isUnguarded();
        Model::reguard();
    }

    protected function tearDown(): void
    {
        if ($this->modelWasUnguarded) {
            Model::unguard();
        } else {
            Model::reguard();
        }

        parent::tearDown();
    }

    public function test_legacy_models_only_mass_assign_explicitly_allowed_attributes(): void
    {
        $models = [
            [PolozeniIspiti::class, 'kandidat_id'],
            [PredmetProgram::class, 'predmet_id'],
            [UpisGodine::class, 'kandidat_id'],
            [Skolarina::class, 'kandidat_id'],
            [UplataSkolarine::class, 'skolarina_id'],
            [Predmet::class, 'naziv'],
            [ZapisnikOPolaganju_Student::class, 'kandidat_id'],
            [ZapisnikOPolaganju_StudijskiProgram::class, 'StudijskiProgram_id'],
            [DiplomskiPrijavaOdbrane::class, 'kandidat_id'],
            [DiplomskiPrijavaTeme::class, 'kandidat_id'],
            [DiplomskiPolaganje::class, 'kandidat_id'],
            [AktivniIspitniRokovi::class, 'naziv'],
            [ZapisnikOPolaganjuIspita::class, 'kandidat_id'],
            [PrijavaIspita::class, 'kandidat_id'],
        ];

        foreach ($models as [$modelClass, $allowedAttribute]) {
            $model = new $modelClass([
                $allowedAttribute => 'allowed',
            ]);

            $this->assertNotNull($model->getAttribute($allowedAttribute));
            $this->assertMassAssignmentRejected($modelClass, 'id');
            $this->assertMassAssignmentRejected($modelClass, 'unlisted_attribute');
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function assertMassAssignmentRejected(string $modelClass, string $attribute): void
    {
        try {
            new $modelClass([$attribute => 'blocked']);
        } catch (MassAssignmentException) {
            return;
        }

        $this->fail("The {$modelClass} model unexpectedly accepted {$attribute}.");
    }
}
