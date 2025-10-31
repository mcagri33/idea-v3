<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentCategory extends Model
{
  use HasFactory,SoftDeletes;

  protected $fillable = [
    'name',
    'description',
    'uuid',
    'slug',
    'order', 
    'template_file'
  ];

  public function documents()
  {
    return $this->hasMany(Document::class, 'category_id');
  }

  protected static function booted()
  {
    static::creating(function ($category) {
      $category->slug = Str::slug($category->name);
    });
  }
	
	public function notes()
	{
    return $this->hasMany(DocumentCategoryNote::class);
	}

}
