<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $user
 */
class AuditLog extends Model
{
    use HasFactory;

    private const SENSITIVE_FIELDS = [
        'name',
        'imeKandidata',
        'prezimeKandidata',
        'imePrezimeJednogRoditelja',
        'jmbg',
        'datumRodjenja',
        'mestoRodjenja',
        'drzavaRodjenja',
        'kontaktTelefon',
        'kontaktTelefonRoditelja',
        'adresaStanovanja',
        'brojIndeksa',
        'email',
        'srednjeSkoleFakulteti',
        'mestoZavrseneSkoleFakulteta',
        'smerZavrseneSkoleFakulteta',
        'drzavaZavrseneSkole',
        'godinaZavrsetkaSkole',
        'telesnaTezina',
        'visina',
        'password',
        'remember_token',
        'api_token',
        'file_path',
        'file_name',
        'notes',
        'slika',
    ];

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach ($values as $key => $value) {
            if (self::isSensitiveField((string) $key)) {
                unset($values[$key]);

                continue;
            }

            if (is_array($value)) {
                $values[$key] = self::sanitizeValues($value);
            }
        }

        return $values;
    }

    /**
     * @return array<string, array{old: mixed, new: mixed, old_exists: bool, new_exists: bool}>
     */
    public function changes(): array
    {
        $oldValues = self::sanitizeValues($this->old_values ?? []) ?? [];
        $newValues = self::sanitizeValues($this->new_values ?? []) ?? [];
        $fields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        $changes = [];

        foreach ($fields as $field) {
            $oldExists = array_key_exists($field, $oldValues);
            $newExists = array_key_exists($field, $newValues);
            $oldValue = $oldValues[$field] ?? null;
            $newValue = $newValues[$field] ?? null;

            if ($oldExists !== $newExists || $oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'old_exists' => $oldExists,
                    'new_exists' => $newExists,
                ];
            }
        }

        return $changes;
    }

    private static function isSensitiveField(string $field): bool
    {
        $normalizedField = strtolower($field);
        $sensitiveFields = array_map('strtolower', self::SENSITIVE_FIELDS);

        return in_array($normalizedField, $sensitiveFields, true)
            || str_contains($normalizedField, 'password')
            || str_contains($normalizedField, 'token')
            || str_contains($normalizedField, 'jmbg')
            || str_contains($normalizedField, 'email')
            || str_contains($normalizedField, 'telefon')
            || str_contains($normalizedField, 'adresa');
    }
}
