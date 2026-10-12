<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedDevice extends Model
{
    protected $table = 'trusted_devices';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'device_token_hash',
        'verified_at',
        'expires_at',
        'created_at',
        'last_used_at',
    ];

    protected $hidden = [
        'device_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'account_id',
            'id'
        );
    }
}