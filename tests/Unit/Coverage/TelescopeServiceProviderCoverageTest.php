<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TelescopeServiceProviderCoverageTest extends TestCase
{
    #[Test]
    public function register_and_gate_methods_are_covered(): void
    {
        $provider = new class($this->app) extends TelescopeServiceProvider
        {
            public function callGatePublic(): void
            {
                $this->gate();
            }
        };

        // Should execute register() and protected hideSensitiveRequestDetails() without throwing.
        $provider->register();

        $provider->callGatePublic();

        $user = new User(['email' => 'no-access@test.local']);
        $this->assertFalse(Gate::forUser($user)->allows('viewTelescope'));
    }
}
