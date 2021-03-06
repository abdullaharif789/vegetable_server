<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table="transactions";
    protected $fillable = [
      'party_id',
      'amount',
      'paid',
      'date',
      'purchase_invoice_id',
      'custom_purchase_invoice_id'
    ];
    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
