<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FraudCase extends Model
{
    use HasFactory;

    protected $table = 'cases'; // تحديد اسم الجدول يدوياً

    protected $fillable = ['title', 'assigned_to', 'status', 'priority'];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'case_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CaseAttachment::class, 'case_id');
    }

    public function aiProposals(): HasMany
    {
        return $this->hasMany(AiProposal::class, 'case_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CaseNote::class, 'case_id');
    }
}