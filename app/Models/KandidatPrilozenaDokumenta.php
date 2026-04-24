<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KandidatPrilozenaDokumenta extends Model
{
    use Auditable, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NEEDS_REVISION = 'needs_revision';

    protected $table = 'kandidat_prilozena_dokumenta';

    protected $fillable = [
        'kandidat_id',
        'prilozenaDokumenta_id',
        'indikatorAktivan',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'review_status',
        'reviewer_id',
        'notes',
        'reviewed_at',
    ];

    protected $attributes = [
        'review_status' => self::STATUS_PENDING,
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function dokument(): BelongsTo
    {
        return $this->belongsTo(PrilozenaDokumenta::class, 'prilozenaDokumenta_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('review_status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('review_status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('review_status', self::STATUS_REJECTED);
    }

    public function scopeNeedsRevision(Builder $query): Builder
    {
        return $query->where('review_status', self::STATUS_NEEDS_REVISION);
    }
}
