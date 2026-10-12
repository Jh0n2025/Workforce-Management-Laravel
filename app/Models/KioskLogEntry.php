<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KioskLogEntry extends Model
{
    protected $table = 'kiosk_log_entries';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'rfid_card_id',
        'rfid_uid',
        'kiosk_name',
        'scan_status',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
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

    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(
            RfidCard::class,
            'rfid_card_id',
            'id'
        );
    }
}