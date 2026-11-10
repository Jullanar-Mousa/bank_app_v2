<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseNote extends Model
{
    use HasFactory;

    protected $fillable = ['case_id', 'user_id', 'note', 'is_ai_generated'];

    protected function casts(): array
    {
        return ['is_ai_generated' => 'boolean'];
    }

    public function fraudCase()
    {
        return $this->belongsTo(FraudCase::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}