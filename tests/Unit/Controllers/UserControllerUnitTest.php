<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Tests\TestCase;

class UserControllerUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_toggle_status_executes_update_and_returns_redirect_response(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->active = 0;

        $user->shouldReceive('update')
            ->once()
            ->with(['active' => true])
            ->andReturnUsing(function () use ($user): bool {
                $user->active = 1;

                return true;
            });

        $response = (new UserController)->toggleStatus($user);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
