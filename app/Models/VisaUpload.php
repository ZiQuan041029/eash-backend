<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaUpload extends Model
{
    protected $fillable = [
        'guest_id',
        'guest_email',
        'user_id',
        'r2_key',
        'status',
        'approved_at',
        'approval_token_hash',
        'approval_token_used_at',
        'verified_at',
        'verified_by',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'guest_id' => 'string',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'approval_token_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

