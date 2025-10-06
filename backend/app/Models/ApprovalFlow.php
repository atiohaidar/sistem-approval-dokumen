<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_template_default',
        'created_by',
    ];

    protected $casts = [
        'is_template_default' => 'boolean',
    ];

    /**
     * Get the user who created this approval flow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all approval steps for this flow.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'flow_id')->orderBy('step_order');
    }

    /**
     * Get all document approvals using this flow.
     */
    public function documentApprovals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }
}
