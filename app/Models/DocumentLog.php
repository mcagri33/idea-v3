<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentLog extends Model
{
  use HasFactory;

  protected $fillable = [
    'document_id',
    'action',
    'performed_by',
    'note',
  ];

  public function document()
  {
    return $this->belongsTo(Document::class);
  }

  public function performedBy()
  {
    return $this->belongsTo(User::class, 'performed_by');
  }
}
