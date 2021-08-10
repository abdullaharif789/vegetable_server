<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table="orders";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'party_id',
        'cart',
        'total',
        'status',
        'order_code',
        'total_tax',
        'manual',
        'bank',
        'van_id'
    ];
    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
