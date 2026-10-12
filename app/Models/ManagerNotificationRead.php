<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerNotificationRead extends Model
{
    protected $table = 'manager_notification_reads';

    public $timestamps = false;

    protected $fillable = [
        'manager_id',
        'notification_type',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'manager_id',
            'id'
        );
    }
}