<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['device_fingerprint', 'device_type', 'sim_changed_at', 'last_ip', 'last_location'];

    protected function casts(): array
    {
        return [
            'sim_changed_at' => 'datetime',
        ];
    }

    public function accountEvents(): HasMany
    {
        return $this->hasMany(AccountEvent::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function customerDevices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class);
    }
}