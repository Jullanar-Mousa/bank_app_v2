<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleSuggestion extends Model
{
    use HasFactory;

    protected $fillable = ['suggested_by', 'title', 'description', 'conditions', 'status', 'reviewed_by', 'reviewed_at', 'review_note', 'rule_id'];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function suggester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
