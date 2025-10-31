<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'action_type',
    'model_type',
    'description',
    'company_name',
    'approved_at',
    'file_created_at'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
