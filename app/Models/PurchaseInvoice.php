<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;
    protected $table="purchase_invoices";
    /**
     * The attributes that are mass assignable.
     *
     * @var arrays
     */
    protected $fillable = [
        'party_id',
        'cart',
        'van_id',
        'total',
        'bank',
        'purchase_order_id',
        'status',
        'discount',
        'created_at'
    ];
    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
