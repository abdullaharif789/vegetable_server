<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table="items";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'image',
      'category_id',
      'tax',
      'visible'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
