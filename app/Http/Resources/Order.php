<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $cart=json_decode($this->cart);
        return [
            'id'=>$this->id,
            'party_id'=>$this->party_id,
            'cart'=>$cart,
            'total_items'=>count($cart),
            'total'=>number_format((float)$this->total, 2, '.', ''),
        ];
        return parent::toArray($request);
    }
}
