<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "party_id"=>$this->party_id,
            "amount"=>number_format((float)$this->amount, 2, '.', ','),
            'paid_boolean'=>$this->paid?true:false,
            'paid'=>$this->paid?"Paid":"Unpaid",
            "date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London')
        ];
        return parent::toArray($request);
    }
}