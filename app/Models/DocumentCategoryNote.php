<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategoryNote extends Model
{
    protected $fillable = ['user_id', 'document_category_id', 'note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }
}
