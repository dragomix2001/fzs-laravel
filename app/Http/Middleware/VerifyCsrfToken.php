<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    protected $except = [
        'izvestaji/spisakZaSmer',
        'izvestaji/nastavniPlan',
        'izvestaji/spisakPoGodini',
        'izvestaji/spisakPoProgramu',
        'izvestaji/spisakPoPredmetima',
        'izvestaji/spisakPoSlavama',
        'izvestaji/spisakPoProfesorima',
        'izvestaji/spisakPoSmerovimaAktivni',
        'izvestaji/spisakPoSmerovimaOstali',
        'izvestaji/excelStampa',
        'izvestaji/diplomaAdd',
        'izvestaji/diplomskiAdd',
    ];
}
