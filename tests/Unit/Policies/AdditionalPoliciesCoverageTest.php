<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Policies\KandidatPolicy;
use App\Policies\PolozeniIspitiPolicy;
use App\Policies\PrijavaIspitaPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdditionalPoliciesCoverageTest extends TestCase
{
    private function user(string $role): User
    {
        return new User(['role' => $role, 'email' => $role.'@test.local']);
    }

    #[Test]
    public function kandidat_policy_covers_all_paths(): void
    {
        $policy = new KandidatPolicy;
        $admin = $this->user('admin');
        $secretary = $this->user('secretary');
        $student = $this->user('student');
        $kandidat = new Kandidat;

        $this->assertTrue($policy->viewAny($student));
        $this->assertTrue($policy->view($student, $kandidat));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->create($secretary));
        $this->assertFalse($policy->create($student));
        $this->assertTrue($policy->update($admin, $kandidat));
        $this->assertTrue($policy->update($secretary, $kandidat));
        $this->assertFalse($policy->update($student, $kandidat));
        $this->assertTrue($policy->delete($admin, $kandidat));
        $this->assertFalse($policy->delete($student, $kandidat));
    }

    #[Test]
    public function prijava_ispita_policy_covers_admin_professor_student_and_fallback(): void
    {
        $policy = new PrijavaIspitaPolicy;

        $admin = $this->user('admin');
        $professor = $this->user('professor');
        $student = $this->user('student');
        $guest = $this->user('guest');

        $professor->setRelation('profesor', (new Profesor)->forceFill(['id' => 11]));
        $student->setRelation('kandidat', (new Kandidat)->forceFill(['id' => 22]));

        $prijavaForProfessor = new PrijavaIspita(['profesor_id' => 11, 'kandidat_id' => 99]);
        $prijavaForStudent = new PrijavaIspita(['profesor_id' => 77, 'kandidat_id' => 22]);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($professor));
        $this->assertFalse($policy->viewAny($student));

        $this->assertTrue($policy->view($admin, $prijavaForProfessor));
        $this->assertTrue($policy->view($professor, $prijavaForProfessor));
        $this->assertTrue($policy->view($student, $prijavaForStudent));
        $this->assertFalse($policy->view($guest, $prijavaForProfessor));

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->create($student));
        $this->assertFalse($policy->create($professor));

        $this->assertTrue($policy->update($admin, $prijavaForProfessor));
        $this->assertTrue($policy->update($student, $prijavaForStudent));
        $this->assertFalse($policy->update($professor, $prijavaForStudent));

        $this->assertTrue($policy->delete($admin, $prijavaForProfessor));
        $this->assertFalse($policy->delete($student, $prijavaForProfessor));
    }

    #[Test]
    public function polozeni_ispiti_policy_covers_all_decision_paths(): void
    {
        $policy = new PolozeniIspitiPolicy;

        $admin = $this->user('admin');
        $professor = $this->user('professor');
        $student = $this->user('student');
        $guest = $this->user('guest');

        $professor->setRelation('profesor', (new Profesor)->forceFill(['id' => 7]));
        $student->setRelation('kandidat', (new Kandidat)->forceFill(['id' => 8]));

        $zapisnikOwned = new ZapisnikOPolaganjuIspita(['profesor_id' => 7]);
        $zapisnikOther = new ZapisnikOPolaganjuIspita(['profesor_id' => 70]);

        $polozeniOwned = new PolozeniIspiti(['kandidat_id' => 8]);
        $polozeniOwned->setRelation('zapisnik', $zapisnikOwned);

        $polozeniOther = new PolozeniIspiti(['kandidat_id' => 80]);
        $polozeniOther->setRelation('zapisnik', $zapisnikOther);

        $polozeniWithoutZapisnik = new PolozeniIspiti(['kandidat_id' => 8]);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($professor));
        $this->assertFalse($policy->viewAny($student));

        $this->assertTrue($policy->view($admin, $polozeniOther));
        $this->assertTrue($policy->view($professor, $polozeniOwned));
        $this->assertFalse($policy->view($professor, $polozeniWithoutZapisnik));
        $this->assertTrue($policy->view($student, $polozeniOwned));
        $this->assertFalse($policy->view($guest, $polozeniOwned));

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->create($professor));
        $this->assertFalse($policy->create($student));

        $this->assertTrue($policy->update($admin, $polozeniOther));
        $this->assertTrue($policy->update($professor, $polozeniOwned));
        $this->assertFalse($policy->update($professor, $polozeniWithoutZapisnik));
        $this->assertFalse($policy->update($student, $polozeniOwned));

        $this->assertTrue($policy->delete($admin, $polozeniOwned));
        $this->assertFalse($policy->delete($student, $polozeniOwned));
    }
}
