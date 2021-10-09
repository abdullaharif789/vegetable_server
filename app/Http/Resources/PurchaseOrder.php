<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
        return[
            'id'=>$this->id,
            "party_id"=>$this->party_id,
            "cart"=>json_decode($this->cart),
            "van"=> $this->van_id,
            "created_at"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y')
        ];
        return parent::toArray($request);
    }
}