<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $fillable = [
        'case_id',
        'risk_score_id',
        'status',
        'priority',
        'assigned_to',
        'decision',
        'decision_reason',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function riskScore(): BelongsTo
    {
        return $this->belongsTo(RiskScore::class, 'risk_score_id');
    }

    public function fraudCase(): BelongsTo
    {
        return $this->belongsTo(FraudCase::class, 'case_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /**
     * جلب المعاملة المرتبطة بالتنبيه عبر سجل تقييم المخاطر (hasOneThrough)
     */
    public function transaction(): HasOneThrough
    {
        return $this->hasOneThrough(
            Transaction::class,
            RiskScore::class,
            'id',             // المفتاح الأساسي في risk_scores (يطابق risk_score_id في alerts)
            'id',             // المفتاح الأساسي في transactions
            'risk_score_id',  // المفتاح الخارجي في alerts
            'transaction_id'  // المفتاح الخارجي في risk_scores
        );
    }
}