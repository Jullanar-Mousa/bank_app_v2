<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountEvent extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'device_id', 'event_type', 'ip_address', 'details', 'event_time', 'idempotency_key'];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'event_time' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function riskScore(): HasOne
    {
        return $this->hasOne(RiskScore::class, 'event_id');
    }
}