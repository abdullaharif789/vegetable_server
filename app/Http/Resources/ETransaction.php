<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ETransaction extends JsonResource
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
            "party_name"=>$this->party?ucwords($this->party->business_name):null,
            "amount"=>number_format((float)$this->amount, 2, '.', ','),
            'paid_boolean'=>$this->paid?true:false,
            'paid'=>$this->paid?"Paid":"Unpaid",
            "date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "new_date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London'),
        ];
        return parent::toArray($request);
    }
}
