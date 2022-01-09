<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Transaction;

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
        $transactions=Transaction::where("party_id",$this->party_id)->where("paid",0)->get(["amount","date"]);
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
            "total"=> number_format($this->total, 2, '.', ''),
            "created_at"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "transactions"=>$transactions
        ];
        return parent::toArray($request);
    }
}
