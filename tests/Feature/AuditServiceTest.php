<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use DatabaseTransactions;

    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        $this->auditService = app(AuditService::class);
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    // ======================== log() Tests ========================

    /**
     * Test logging with authenticated user
     */
    public function test_log_creates_entry_with_authenticated_user(): void
    {
        $user = User::create(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password']);
        $this->actingAs($user);

        $request = $this->createMockRequest('192.168.1.1', 'Mozilla/5.0');

        $this->auditService->log(
            $request,
            'CREATE',
            'users',
            1,
            [],
            ['name' => 'John Doe', 'email' => 'john@test.com']
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertEquals(1, $log->user_id);
        $this->assertEquals('CREATE', $log->action);
        $this->assertEquals('users', $log->table_name);
        $this->assertEquals(1, $log->record_id);
        $this->assertEquals('192.168.1.1', $log->ip_address);
        $this->assertNotNull($log->user_agent);
        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@test.com'], $log->new_values);
    }

    /**
     * Test logging without authenticated user (null userId)
     */
    public function test_log_creates_entry_without_authenticated_user(): void
    {
        $request = $this->createMockRequest('192.168.1.100', 'Chrome/5.0');

        $this->auditService->log(
            $request,
            'UPDATE',
            'products',
            5,
            ['price' => 100],
            ['price' => 150]
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertNull($log->user_id);
        $this->assertEquals('UPDATE', $log->action);
        $this->assertEquals('products', $log->table_name);
        $this->assertEquals(5, $log->record_id);
        $this->assertEquals('192.168.1.100', $log->ip_address);
        $this->assertEquals(['price' => 100], $log->old_values);
        $this->assertEquals(['price' => 150], $log->new_values);
    }

    /**
     * Test logging with null request object
     */
    public function test_log_creates_entry_with_null_request(): void
    {
        $user = User::create(['id' => 2, 'name' => 'Test User 2', 'email' => 'test2@example.com', 'password' => 'password']);
        $this->actingAs($user);

        $this->auditService->log(
            null,
            'DELETE',
            'posts',
            10,
            ['title' => 'Old Post', 'body' => 'Old body'],
            []
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertEquals(2, $log->user_id);
        $this->assertEquals('DELETE', $log->action);
        $this->assertNull($log->ip_address);
        $this->assertNull($log->user_agent);
        $this->assertEquals(['title' => 'Old Post', 'body' => 'Old body'], $log->old_values);
    }

    /**
     * Test logging with empty old/new values
     */
    public function test_log_creates_entry_with_empty_values(): void
    {
        $request = $this->createMockRequest('127.0.0.1', 'Safari');

        $this->auditService->log(
            $request,
            'VIEW',
            'documents',
            20,
            [],
            []
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertEquals('VIEW', $log->action);
        $this->assertEquals([], $log->old_values);
        $this->assertEquals([], $log->new_values);
    }

    /**
     * Test logging with complex nested array values
     */
    public function test_log_creates_entry_with_nested_array_values(): void
    {
        $user = User::create(['id' => 3, 'name' => 'Test User 3', 'email' => 'test3@example.com', 'password' => 'password']);
        $this->actingAs($user);

        $request = $this->createMockRequest('10.0.0.1', 'Firefox');

        $oldValues = [
            'metadata' => [
                'tags' => ['old', 'tags'],
                'nested' => ['deep' => ['value' => 'old']],
            ],
        ];

        $newValues = [
            'metadata' => [
                'tags' => ['new', 'tags'],
                'nested' => ['deep' => ['value' => 'new']],
            ],
        ];

        $this->auditService->log(
            $request,
            'BULK_UPDATE',
            'configs',
            99,
            $oldValues,
            $newValues
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertEquals($oldValues, $log->old_values);
        $this->assertEquals($newValues, $log->new_values);
    }

    /**
     * Test logging with authenticated user and null request
     */
    public function test_log_creates_entry_with_user_and_null_request(): void
    {
        $user = User::create(['id' => 4, 'name' => 'Test User 4', 'email' => 'test4@example.com', 'password' => 'password']);
        $this->actingAs($user);

        $this->auditService->log(
            null,
            'EXPORT',
            'reports',
            50,
            [],
            ['status' => 'exported']
        );

        $log = AuditLog::first();

        $this->assertNotNull($log);
        $this->assertEquals(4, $log->user_id);
        $this->assertEquals('EXPORT', $log->action);
        $this->assertNull($log->ip_address);
        $this->assertNull($log->user_agent);
    }

    /**
     * Test logging with various actions
     */
    public function test_log_creates_entries_with_various_actions(): void
    {
        $request = $this->createMockRequest('172.16.0.1', 'Edge');
        $actions = ['CREATE', 'READ', 'UPDATE', 'DELETE', 'EXPORT', 'IMPORT'];

        foreach ($actions as $i => $action) {
            $this->auditService->log(
                $request,
                $action,
                'test_table',
                $i + 1,
                [],
                []
            );
        }

        $this->assertEqualsCanonicalizing(6, AuditLog::count());

        foreach ($actions as $i => $action) {
            $log = AuditLog::where('action', $action)->first();
            $this->assertNotNull($log);
            $this->assertEquals($action, $log->action);
        }
    }

    // ======================== getLogs() Tests ========================

    /**
     * Test retrieving all logs without filters
     */
    public function test_getLogs_returns_all_logs_without_filters(): void
    {
        $user1 = User::create(['id' => 100, 'name' => 'User 100', 'email' => 'user100@example.com', 'password' => 'password']);
        $user2 = User::create(['id' => 101, 'name' => 'User 101', 'email' => 'user101@example.com', 'password' => 'password']);

        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->actingAs($user1);
        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);
        $this->auditService->log($request, 'UPDATE', 'products', 2, [], []);

        $this->actingAs($user2);
        $this->auditService->log($request, 'DELETE', 'orders', 3, [], []);

        $logs = $this->auditService->getLogs();

        $this->assertEqualsCanonicalizing(3, $logs->count());
    }

    /**
     * Test retrieving logs filtered by table name
     */
    public function test_getLogs_filters_by_tableName(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);
        $this->auditService->log($request, 'CREATE', 'users', 2, [], []);
        $this->auditService->log($request, 'CREATE', 'products', 1, [], []);
        $this->auditService->log($request, 'CREATE', 'orders', 1, [], []);

        $userLogs = $this->auditService->getLogs('users');

        $this->assertEqualsCanonicalizing(2, $userLogs->count());
        $userLogs->each(function ($log) {
            $this->assertEquals('users', $log->table_name);
        });
    }

    /**
     * Test retrieving logs filtered by userId
     */
    public function test_getLogs_filters_by_userId(): void
    {
        $user1 = User::create(['id' => 200, 'name' => 'User 200', 'email' => 'user200@example.com', 'password' => 'password']);
        $user2 = User::create(['id' => 201, 'name' => 'User 201', 'email' => 'user201@example.com', 'password' => 'password']);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->actingAs($user1);
        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);
        $this->auditService->log($request, 'UPDATE', 'users', 2, [], []);

        $this->actingAs($user2);
        $this->auditService->log($request, 'DELETE', 'users', 3, [], []);

        $user1Logs = $this->auditService->getLogs(null, 200);

        $this->assertEqualsCanonicalizing(2, $user1Logs->count());
        $user1Logs->each(function ($log) {
            $this->assertEquals(200, $log->user_id);
        });
    }

    /**
     * Test retrieving logs with combined filters (tableName and userId)
     */
    public function test_getLogs_filters_by_combined_tableName_and_userId(): void
    {
        $user1 = User::create(['id' => 300, 'name' => 'User 300', 'email' => 'user300@example.com', 'password' => 'password']);
        $user2 = User::create(['id' => 301, 'name' => 'User 301', 'email' => 'user301@example.com', 'password' => 'password']);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->actingAs($user1);
        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);
        $this->auditService->log($request, 'CREATE', 'products', 2, [], []);

        $this->actingAs($user2);
        $this->auditService->log($request, 'CREATE', 'users', 3, [], []);
        $this->auditService->log($request, 'CREATE', 'products', 4, [], []);

        $combinedLogs = $this->auditService->getLogs('users', 300);

        $this->assertEqualsCanonicalizing(1, $combinedLogs->count());
        $this->assertEquals(300, $combinedLogs->first()->user_id);
        $this->assertEquals('users', $combinedLogs->first()->table_name);
    }

    /**
     * Test retrieving logs with limit parameter
     */
    public function test_getLogs_respects_limit_parameter(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        // Create 15 logs
        for ($i = 0; $i < 15; $i++) {
            $this->auditService->log($request, 'CREATE', 'test_table', $i, [], []);
        }

        // Default limit is 100, so all should return
        $allLogs = $this->auditService->getLogs();
        $this->assertEqualsCanonicalizing(15, $allLogs->count());

        // Test custom limit
        $limitedLogs = $this->auditService->getLogs(null, null, 5);
        $this->assertEqualsCanonicalizing(5, $limitedLogs->count());

        $limitedLogs10 = $this->auditService->getLogs(null, null, 10);
        $this->assertEqualsCanonicalizing(10, $limitedLogs10->count());
    }

    /**
     * Test retrieving logs are ordered by created_at descending
     */
    public function test_getLogs_orders_by_created_at_descending(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->auditService->log($request, 'CREATE', 'test', 1, [], []);
        sleep(1);
        $this->auditService->log($request, 'UPDATE', 'test', 2, [], []);
        sleep(1);
        $this->auditService->log($request, 'DELETE', 'test', 3, [], []);

        $logs = $this->auditService->getLogs();

        $this->assertEqualsCanonicalizing(3, $logs->count());

        $this->assertEquals('DELETE', $logs[0]->action);
        $this->assertEquals(3, $logs[0]->record_id);

        $this->assertEquals('CREATE', $logs[2]->action);
        $this->assertEquals(1, $logs[2]->record_id);
    }

    /**
     * Test getLogs with table filter and limit
     */
    public function test_getLogs_filters_and_limits_combined(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        for ($i = 0; $i < 5; $i++) {
            $this->auditService->log($request, 'CREATE', 'users', $i, [], []);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->auditService->log($request, 'CREATE', 'products', $i, [], []);
        }

        $userLogs = $this->auditService->getLogs('users', null, 3);

        $this->assertEqualsCanonicalizing(3, $userLogs->count());
        $userLogs->each(function ($log) {
            $this->assertEquals('users', $log->table_name);
        });
    }

    /**
     * Test getLogs with userId filter and limit
     */
    public function test_getLogs_filters_by_userId_and_limit(): void
    {
        $user1 = User::create(['id' => 400, 'name' => 'User 400', 'email' => 'user400@example.com', 'password' => 'password']);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->actingAs($user1);
        for ($i = 0; $i < 20; $i++) {
            $this->auditService->log($request, 'CREATE', 'test', $i, [], []);
        }

        $userLogs = $this->auditService->getLogs(null, 400, 7);

        $this->assertEqualsCanonicalizing(7, $userLogs->count());
        $userLogs->each(function ($log) {
            $this->assertEquals(400, $log->user_id);
        });
    }

    /**
     * Test getLogs returns empty collection when no matches
     */
    public function test_getLogs_returns_empty_when_no_matches(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);

        // Filter by non-existent table
        $logs = $this->auditService->getLogs('nonexistent_table');
        $this->assertEqualsCanonicalizing(0, $logs->count());

        // Filter by non-existent userId
        $logs = $this->auditService->getLogs(null, 9999);
        $this->assertEqualsCanonicalizing(0, $logs->count());
    }

    /**
     * Test getLogs eager loads user relationship
     */
    public function test_getLogs_eager_loads_user_relationship(): void
    {
        $user = User::create(['id' => 500, 'name' => 'User 500', 'email' => 'user500@example.com', 'password' => 'password']);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->actingAs($user);
        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);

        $logs = $this->auditService->getLogs();

        $this->assertNotNull($logs->first()->user);
        $this->assertEquals(500, $logs->first()->user->id);
    }

    /**
     * Test getLogs with null userId in database
     */
    public function test_getLogs_handles_null_userId_entries(): void
    {
        $user = User::create(['id' => 600, 'name' => 'User 600', 'email' => 'user600@example.com', 'password' => 'password']);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla');

        $this->auditService->log($request, 'CREATE', 'users', 1, [], []);

        $this->actingAs($user);
        $this->auditService->log($request, 'UPDATE', 'users', 2, [], []);

        $allLogs = $this->auditService->getLogs();
        $this->assertEqualsCanonicalizing(2, $allLogs->count());

        $userLogs = $this->auditService->getLogs(null, 600);
        $this->assertEqualsCanonicalizing(1, $userLogs->count());
        $this->assertEquals(600, $userLogs->first()->user_id);
    }

    // ======================== Helper Methods ========================

    /**
     * Create a mock Request object with specified IP and User Agent
     */
    private function createMockRequest(string $ip, string $userAgent): Request
    {
        $request = Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', $ip);
        $request->server->set('HTTP_USER_AGENT', $userAgent);

        return $request;
    }
}
