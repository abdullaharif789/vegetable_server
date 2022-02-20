<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Http\Resources\ETransaction as TransactionResource;
class PurchaseInvoice extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $transactions_total=0.0;
        $transactions = Transaction::where("party_id",$this->party_id)->where("paid",0)->get(["amount","date","purchase_invoice_id"]);
        foreach ($transactions as $key => $value) {
            $transactions_total+=(float)$value->amount;
        }
        
        return[
            'id'=>$this->id,
            "party_id"=>$this->party_id,
            "party"=>[
                'id'=>$this->party->id,
                'business_name'=>ucwords($this->party->business_name),
                'address'=>ucwords($this->party->address),
                'contact_number'=>$this->party->contact_number,
            ],
            "cart"=>json_decode($this->cart),
            "sr"=>$this->sr,
            "van"=> $this->van_id,
            "created_at"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "bank_visible"=>$this->bank,
            "status"=>ucwords($this->status),
            "purchase_order_id"=>$this->purchase_order_id,
            "discount"=> ($this->discount * 100)."%",
            "discount_amount"=>number_format($this->total*$this->discount, 2, '.', ''),
            "total"=> number_format($this->total - $this->total*$this->discount, 2, '.', ''),
            "total_without_discount"=> number_format($this->total, 2, '.', ''),
            "transactions"=> $transactions,
            "transactions_total"=>$transactions_total
        ];
    }
}
