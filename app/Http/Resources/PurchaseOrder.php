<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Http\Resources\ETransaction as TransactionResource;
class PurchaseOrder extends JsonResource
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
        $transactions=Transaction::where("party_id",$this->party_id)->where("paid",0)->get(["amount","date","purchase_invoice_id"]);
        $transactions= TransactionResource::collection($transactions);

        $newTransaction=json_decode(json_encode($transactions));
        foreach ($newTransaction as $key => $value) {
            $transactions_total+=(float)$value->amount;
        }

        $total=0;
        $cart=json_decode($this->cart);
        foreach($cart as $item){
            $total+=(float)$item->total;
        }
        $this->total=$total;
        return[
            'id'=>$this->id,
            "party_id"=>$this->party_id,
            "party"=>[
                'id'=>$this->party->id,
                'business_name'=>ucwords($this->party->business_name),
                'address'=>ucwords($this->party->address),
                'contact_number'=>$this->party->contact_number,
            ],
            "cart"=>$cart,
            "sr"=>$this->sr,
            "van"=> $this->van_id,
            "total"=> number_format($this->total, 2, '.', ''),
            "created_at"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "transactions"=>$transactions,
            "transactions_total"=>$transactions_total
        ];
        return parent::toArray($request);
    }
}
