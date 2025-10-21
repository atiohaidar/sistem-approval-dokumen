<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApproval extends Model
{
    protected $fillable = [
        'document_id',
        'approver_id',
        'action',
        'notes',
        'level',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'level' => 'integer',
    ];

    // Relationships
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
