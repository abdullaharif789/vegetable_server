<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Inventory extends JsonResource
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
            "id"=> $this->id,
            "item_id"=> $this->item_id,
            'unit'=> $this->unit,
            'buying_price'=>  $this->buying_price,
            'selling_price'=>  $this->selling_price,
            "date"=> Date('d-M-Y',strtotime($this->stock_date)),
        ];
        return parent::toArray($request);
    }
}
