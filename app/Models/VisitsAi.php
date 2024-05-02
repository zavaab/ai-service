<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitsAi extends Model
{
    use HasFactory;
    protected $table = 'visits_ai';
    protected $fillable = ['visit_id','category_id','ai_id','name','url','status'];
}
