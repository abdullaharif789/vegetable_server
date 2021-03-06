<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $table="expenses";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expense_type_id',
        'amount',
        'date'
    ];
    public function expense_type()
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
