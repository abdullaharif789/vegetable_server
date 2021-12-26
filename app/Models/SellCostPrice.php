<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellCostPrice extends Model
{
    use HasFactory;
    protected $table="sell_cost_prices";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'item_id',
      'item_type',
      'cost_price',
      'price'
    ];
}
