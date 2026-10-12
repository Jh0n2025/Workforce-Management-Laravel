<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidCard extends Model
{
    protected $table = 'rfid_cards';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'rfid_uid',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id',
            'employee_id'
        );
    }

    public function kioskLogEntries()
    {
        return $this->hasMany(
            KioskLogEntry::class,
            'rfid_card_id',
            'id'
        );
    }
}