<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MlModel extends Model
{
    use HasFactory;

    protected $fillable = ['model_name', 'algorithm', 'status'];

    public function modelRuns(): HasMany
    {
        return $this->hasMany(ModelRun::class, 'model_id');
    }
}