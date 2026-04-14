<?php

namespace App\Providers;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Policies\IspitPolicy;
use App\Policies\KandidatPolicy;
use App\Policies\PolozeniIspitiPolicy;
use App\Policies\PrijavaIspitaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kandidat::class => KandidatPolicy::class,
        ZapisnikOPolaganjuIspita::class => IspitPolicy::class,
        PrijavaIspita::class => PrijavaIspitaPolicy::class,
        PolozeniIspiti::class => PolozeniIspitiPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
