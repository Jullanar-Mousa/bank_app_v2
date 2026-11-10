<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 
        'event_id',
        'alert_id',
        'is_fraud', 
        'source', 
        'labeled_by'
    ];

    protected function casts(): array
    {
        return [
            'is_fraud' => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AccountEvent::class, 'event_id');
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    // الموظف الذي قام باتخاذ القرار النهائي
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'labeled_by');
    }
}