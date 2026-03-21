<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';

    protected $fillable = [
        'title',
        'content',
        'category',
        'embedding',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
