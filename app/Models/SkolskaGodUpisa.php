<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkolskaGodUpisa extends Model
{
    use HasFactory;

    protected $table = 'skolska_god_upisa';

    public function kandidati(): HasMany
    {
        return $this->hasMany(Kandidat::class, 'skolskaGodinaUpisa_id');
    }

    public function getGodinaAttribute(): ?int
    {
        if (preg_match('/^(\d{4})/', $this->naziv, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
