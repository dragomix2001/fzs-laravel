<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Profesor;
use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Policies\IspitPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IspitPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private IspitPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->policy = new IspitPolicy;
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function makeUser(string $role, ?string $email = null): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email ?? 'user_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    private function makeZapisnik(int $profesorId): ZapisnikOPolaganjuIspita
    {
        return new ZapisnikOPolaganjuIspita(['profesor_id' => $profesorId]);
    }

    // =========================================================================
    // viewAny
    // =========================================================================

    public function test_view_any_allows_admin(): void
    {
        $user = $this->makeUser('admin');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_any_allows_professor(): void
    {
        $user = $this->makeUser('professor');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_any_denies_student(): void
    {
        $user = $this->makeUser('student');
        $this->assertFalse($this->policy->viewAny($user));
    }

    // =========================================================================
    // view
    // =========================================================================

    public function test_view_allows_admin_regardless_of_profesor_id(): void
    {
        $user = $this->makeUser('admin');
        $zapisnik = $this->makeZapisnik(999);

        $this->assertTrue($this->policy->view($user, $zapisnik));
    }

    public function test_view_allows_professor_who_owns_zapisnik(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id);

        $this->assertTrue($this->policy->view($user, $zapisnik));
    }

    public function test_view_denies_professor_who_does_not_own_zapisnik(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id + 100);

        $this->assertFalse($this->policy->view($user, $zapisnik));
    }

    public function test_view_denies_professor_without_profesor_record(): void
    {
        $user = $this->makeUser('professor');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertFalse($this->policy->view($user, $zapisnik));
    }

    public function test_view_denies_student(): void
    {
        $user = $this->makeUser('student');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertFalse($this->policy->view($user, $zapisnik));
    }

    // =========================================================================
    // create
    // =========================================================================

    public function test_create_allows_admin(): void
    {
        $user = $this->makeUser('admin');
        $this->assertTrue($this->policy->create($user));
    }

    public function test_create_allows_professor(): void
    {
        $user = $this->makeUser('professor');
        $this->assertTrue($this->policy->create($user));
    }

    public function test_create_denies_student(): void
    {
        $user = $this->makeUser('student');
        $this->assertFalse($this->policy->create($user));
    }

    // =========================================================================
    // update
    // =========================================================================

    public function test_update_allows_admin(): void
    {
        $user = $this->makeUser('admin');
        $zapisnik = $this->makeZapisnik(999);

        $this->assertTrue($this->policy->update($user, $zapisnik));
    }

    public function test_update_allows_owning_professor(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id);

        $this->assertTrue($this->policy->update($user, $zapisnik));
    }

    public function test_update_denies_non_owning_professor(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id + 100);

        $this->assertFalse($this->policy->update($user, $zapisnik));
    }

    // =========================================================================
    // delete
    // =========================================================================

    public function test_delete_allows_admin(): void
    {
        $user = $this->makeUser('admin');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertTrue($this->policy->delete($user, $zapisnik));
    }

    public function test_delete_denies_professor(): void
    {
        $user = $this->makeUser('professor');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertFalse($this->policy->delete($user, $zapisnik));
    }

    public function test_delete_denies_student(): void
    {
        $user = $this->makeUser('student');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertFalse($this->policy->delete($user, $zapisnik));
    }

    // =========================================================================
    // arhiviraj
    // =========================================================================

    public function test_arhiviraj_allows_admin(): void
    {
        $user = $this->makeUser('admin');
        $zapisnik = $this->makeZapisnik(999);

        $this->assertTrue($this->policy->arhiviraj($user, $zapisnik));
    }

    public function test_arhiviraj_allows_owning_professor(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id);

        $this->assertTrue($this->policy->arhiviraj($user, $zapisnik));
    }

    public function test_arhiviraj_denies_non_owning_professor(): void
    {
        $profesor = Profesor::factory()->create();

        $user = $this->makeUser('professor', $profesor->mail);
        $zapisnik = $this->makeZapisnik($profesor->id + 100);

        $this->assertFalse($this->policy->arhiviraj($user, $zapisnik));
    }

    public function test_arhiviraj_denies_student(): void
    {
        $user = $this->makeUser('student');
        $zapisnik = $this->makeZapisnik(1);

        $this->assertFalse($this->policy->arhiviraj($user, $zapisnik));
    }
}
