<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitsAiResult extends Model
{
    use HasFactory;
    protected $table = 'visits_ai_results';
    protected $fillable = ['visits_ai_id','code','count'];
}
