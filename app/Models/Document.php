<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
  use HasFactory;
  use SoftDeletes;
  protected $fillable = [
    'user_id',
    'category_id',
    'file_path',
    'status',
    'rejection_note',
    'uuid',
    'document_name',
    'description',
    'file_year'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function category()
  {
    return $this->belongsTo(DocumentCategory::class, 'category_id');
  }

  public function logs()
  {
    return $this->hasMany(DocumentLog::class);
  }
}
