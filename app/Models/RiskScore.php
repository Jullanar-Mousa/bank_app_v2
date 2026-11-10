<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RiskScore extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'event_id', 'model_run_id', 'score', 'decision', 'reasons', 'evaluated_at', 'rule_score', 'model_probability', 'used_fallback', 'latency_ms', 'features'];

    protected function casts(): array
    {
        return [
            'reasons' => 'array',
            'evaluated_at' => 'datetime',
            'features' => 'array',
            'used_fallback' => 'boolean',
            'model_probability' => 'decimal:4',
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

    public function alert(): HasOne
    {
        return $this->hasOne(Alert::class);
    }

    public function modelRun(): BelongsTo
    {
        return $this->belongsTo(ModelRun::class);
    }
}