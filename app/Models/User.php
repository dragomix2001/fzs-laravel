<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    const ROLE_ADMIN = 'admin';
    const ROLE_PROFESSOR = 'professor';
    const ROLE_STUDENT = 'student';

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isProfessor()
    {
        return $this->role === self::ROLE_PROFESSOR;
    }

    public function isStudent()
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasAnyRole($roles)
    {
        return in_array($this->role, $roles);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'email', 'email');
    }

    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'email', 'email');
    }
}
