<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['case_id', 'uploaded_by', 'file_name', 'file_path', 'mime_type', 'file_size'];

    public function fraudCase(): BelongsTo
    {
        return $this->belongsTo(FraudCase::class, 'case_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
