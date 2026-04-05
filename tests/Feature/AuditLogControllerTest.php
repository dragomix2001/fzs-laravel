<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function ($user) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            if ($user && isset($user->role) && $user->role === 'admin') {
                return true;
            }

            return null;
        });
    }

    /**
     * Test unauthenticated user cannot access audit.index
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->get('/audit-logs');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated admin user can view audit logs index
     */
    public function test_authenticated_user_can_view_audit_logs_index(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $response->assertViewIs('audit.index');
        $response->assertViewHas(['logs', 'tables']);
    }

    /**
     * Test index displays audit logs with pagination
     */
    public function test_index_displays_audit_logs_with_pagination(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->count(60)->create();

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $response->assertViewHas('logs');
        $logs = $response->viewData('logs');
        $this->assertCount(50, $logs);
        $this->assertInstanceOf(LengthAwarePaginator::class, $logs);
    }

    /**
     * Test index eager loads user relationship
     */
    public function test_index_eager_loads_user_relationship(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $auditUser = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $auditUser->id]);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertTrue($logs->first()->relationLoaded('user'));
    }

    /**
     * Test filter by table_name works
     */
    public function test_filter_by_table_name_works(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->create(['table_name' => 'users']);
        AuditLog::factory()->create(['table_name' => 'kandidati']);
        AuditLog::factory()->create(['table_name' => 'users']);

        $response = $this->actingAs($user)->get('/audit-logs?table_name=users');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals('users', $log->table_name);
        }
    }

    /**
     * Test filter by user_id works
     */
    public function test_filter_by_user_id_works(): void
    {
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $auditUser1 = User::factory()->create();
        $auditUser2 = User::factory()->create();

        AuditLog::factory()->create(['user_id' => $auditUser1->id]);
        AuditLog::factory()->create(['user_id' => $auditUser2->id]);
        AuditLog::factory()->create(['user_id' => $auditUser1->id]);

        $response = $this->actingAs($adminUser)->get('/audit-logs?user_id='.$auditUser1->id);

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals($auditUser1->id, $log->user_id);
        }
    }

    /**
     * Test filter by both table_name and user_id works
     */
    public function test_filter_by_both_table_name_and_user_id_works(): void
    {
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $auditUser1 = User::factory()->create();
        $auditUser2 = User::factory()->create();

        AuditLog::factory()->create(['user_id' => $auditUser1->id, 'table_name' => 'users']);
        AuditLog::factory()->create(['user_id' => $auditUser2->id, 'table_name' => 'users']);
        AuditLog::factory()->create(['user_id' => $auditUser1->id, 'table_name' => 'kandidati']);

        $response = $this->actingAs($adminUser)->get('/audit-logs?table_name=users&user_id='.$auditUser1->id);

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(1, $logs);
        $this->assertEquals('users', $logs->first()->table_name);
        $this->assertEquals($auditUser1->id, $logs->first()->user_id);
    }

    /**
     * Test index orders logs by created_at descending
     */
    public function test_index_orders_logs_by_created_at_desc(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $log1 = AuditLog::factory()->create(['created_at' => now()->subDays(3)]);
        $log2 = AuditLog::factory()->create(['created_at' => now()->subDays(1)]);
        $log3 = AuditLog::factory()->create(['created_at' => now()->subDays(2)]);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertEquals($log2->id, $logs[0]->id);
        $this->assertEquals($log3->id, $logs[1]->id);
        $this->assertEquals($log1->id, $logs[2]->id);
    }

    /**
     * Test index provides distinct table names
     */
    public function test_index_provides_distinct_table_names(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->create(['table_name' => 'users']);
        AuditLog::factory()->create(['table_name' => 'kandidati']);
        AuditLog::factory()->create(['table_name' => 'users']);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $tables = $response->viewData('tables');
        $this->assertCount(2, $tables);
        $this->assertTrue($tables->contains('users'));
        $this->assertTrue($tables->contains('kandidati'));
    }

    /**
     * Test empty table_name filter returns all logs
     */
    public function test_empty_table_name_filter_returns_all_logs(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/audit-logs?table_name=');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(3, $logs);
    }

    /**
     * Test empty user_id filter returns all logs
     */
    public function test_empty_user_id_filter_returns_all_logs(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/audit-logs?user_id=');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(3, $logs);
    }

    /**
     * Test no logs returns empty paginated collection
     */
    public function test_no_logs_returns_empty_paginated_collection(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(0, $logs);
        $this->assertInstanceOf(LengthAwarePaginator::class, $logs);
    }

    /**
     * Test filter with non-existent table_name returns empty
     */
    public function test_filter_with_non_existent_table_name_returns_empty(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->create(['table_name' => 'users']);

        $response = $this->actingAs($user)->get('/audit-logs?table_name=non_existent');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(0, $logs);
    }

    /**
     * Test filter with non-existent user_id returns empty
     */
    public function test_filter_with_non_existent_user_id_returns_empty(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->create(['user_id' => 1]);

        $response = $this->actingAs($user)->get('/audit-logs?user_id=99999');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(0, $logs);
    }

    /**
     * Test index handles null user relationship gracefully
     */
    public function test_index_handles_null_user_relationship_gracefully(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        AuditLog::factory()->create(['user_id' => null]);

        $response = $this->actingAs($user)->get('/audit-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertCount(1, $logs);
        $this->assertNull($logs->first()->user);
    }
}
