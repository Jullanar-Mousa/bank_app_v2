<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProposal extends Model
{
    use HasFactory;

    protected $fillable = ['case_id', 'alert_id', 'tool_name', 'payload', 'status', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function fraudCase(): BelongsTo
    {
        return $this->belongsTo(FraudCase::class, 'case_id');
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
