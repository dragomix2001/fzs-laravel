<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;

class IspitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('professor');
    }

    public function view(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;

            return $profesor !== null && $zapisnik->profesor_id === $profesor->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('professor');
    }

    public function update(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;

            return $profesor !== null && $zapisnik->profesor_id === $profesor->id;
        }

        return false;
    }

    public function delete(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        return $user->hasRole('admin');
    }

    public function arhiviraj(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;

            return $profesor !== null && $zapisnik->profesor_id === $profesor->id;
        }

        return false;
    }
}
