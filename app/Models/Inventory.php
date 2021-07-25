<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table="inventories";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'unit',
        'buying_price',
        'selling_price',
        'stock_date',
        'remaining_unit'
    ];
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
