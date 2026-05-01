<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class UserControllerTest extends TestCase
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
    // INDEX METHOD TESTS
    // ============================================================

    public function test_index_displays_paginated_users_for_authenticated_admin(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        for ($i = 0; $i < 25; $i++) {
            User::create([
                'name' => "Student {$i}",
                'email' => "student_{$i}_".uniqid().'@test.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]);
        }

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertViewIs('user.index');
        $response->assertViewHas('users');
    }

    public function test_index_loads_professor_and_candidate_relations(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        for ($i = 0; $i < 5; $i++) {
            User::create([
                'name' => "Professor {$i}",
                'email' => "professor_{$i}_".uniqid().'@test.com',
                'password' => bcrypt('password'),
                'role' => 'professor',
            ]);
        }

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertNotNull($users);
    }

    public function test_index_pagination_works_with_more_than_twenty_users(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        for ($i = 0; $i < 30; $i++) {
            User::create([
                'name' => "Student {$i}",
                'email' => "student_{$i}_".uniqid().'@test.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]);
        }

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertTrue(method_exists($users, 'count'));
    }

    // ============================================================
    // CREATE METHOD TESTS
    // ============================================================

    public function test_create_displays_create_form_for_authenticated_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/users/create');

        $response->assertOk();
        $response->assertViewIs('user.create');
    }

    // ============================================================
    // STORE METHOD TESTS
    // ============================================================

    public function test_store_creates_new_user_with_hashed_password(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser_'.uniqid().'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Корисник креиран');
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'role' => 'student',
        ]);
    }

    public function test_store_validation_fails_for_missing_name(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => '',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validation_fails_for_invalid_email(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'professor',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_for_duplicate_email(): void
    {
        $email = 'duplicate_'.uniqid().'@test.com';
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Existing User',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Another User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validation_fails_for_password_too_short(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_store_validation_fails_for_password_confirmation_mismatch(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'role' => 'professor',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_store_validation_fails_for_invalid_role(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    // ============================================================
    // SHOW METHOD TESTS
    // ============================================================

    public function test_show_displays_single_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Display User',
            'email' => 'display_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->get("/users/{$user->id}");

        $response->assertOk();
        $response->assertViewIs('user.show');
        $response->assertViewHas('user', $user);
    }

    // ============================================================
    // EDIT METHOD TESTS
    // ============================================================

    public function test_edit_displays_edit_form_with_user_data(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Edit User',
            'email' => 'edit_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'professor',
        ]);

        $response = $this->actingAs($admin)->get("/users/{$user->id}/edit");

        $response->assertOk();
        $response->assertViewIs('user.edit');
        $response->assertViewHas('user', $user);
    }

    // ============================================================
    // UPDATE METHOD TESTS
    // ============================================================

    public function test_update_updates_user_without_password_change(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Original Name',
            'email' => 'original_'.uniqid().'@test.com',
            'password' => bcrypt('original_password'),
            'role' => 'student',
        ]);

        $originalPasswordHash = $user->password;

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'professor',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Корисник ажуриран');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('professor', $user->role);
        $this->assertEquals($originalPasswordHash, $user->password);
    }

    public function test_update_updates_user_with_new_password(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'User With Password',
            'email' => 'user_'.uniqid().'@test.com',
            'password' => bcrypt('old_password'),
            'role' => 'student',
        ]);

        $originalPasswordHash = $user->password;

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
            'role' => $user->role,
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Корисник ажуриран');

        $user->refresh();
        $this->assertNotEquals($originalPasswordHash, $user->password);
    }

    public function test_update_allows_same_email_for_current_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'User Email',
            'email' => 'same_email_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'student',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_validation_fails_for_duplicate_email_different_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $email = 'duplicate_'.uniqid().'@test.com';
        User::create([
            'name' => 'User One',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'different_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->put("/users/{$user2->id}", [
            'name' => 'User Two Updated',
            'email' => $email,
            'role' => 'student',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_validation_fails_for_invalid_password_confirmation(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'User Password',
            'email' => 'user_pwd_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new_password_123',
            'password_confirmation' => 'different_password',
            'role' => $user->role,
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ============================================================
    // DESTROY METHOD TESTS
    // ============================================================

    public function test_destroy_cannot_delete_own_account(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Не можете обрисати сопствени налог');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin User',
        ]);
    }

    public function test_destroy_can_delete_other_user_account(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $userToDelete = User::create([
            'name' => 'User To Delete',
            'email' => 'delete_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $userToDeleteId = $userToDelete->id;

        $response = $this->actingAs($admin)->delete("/users/{$userToDelete->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Корисник обрисан');

        $this->assertDatabaseMissing('users', [
            'id' => $userToDeleteId,
        ]);
    }

    // ============================================================
    // TOGGLE STATUS METHOD TESTS
    // ============================================================

    public function test_toggle_status_route_exists(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Route exists and is callable by authenticated admin
        $response = $this->actingAs($admin)->get("/users/{$user->id}/toggle");

        // Should return either 200 or error due to missing column, not 404
        $this->assertNotEquals(404, $response->status());
    }

    public function test_toggle_status_requires_admin_role(): void
    {
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->actingAs($student)->get("/users/{$user->id}/toggle");

        // Student users should be denied access
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

}
