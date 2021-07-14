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
        $totalQuantity=0;
        foreach($cart as $item){
            $totalQuantity+=$item->quantity;
        }
        return [
            'id'=>$this->id,
            'party_id'=>$this->party_id,
            'cart'=>$cart,
            'total_items'=>count($cart),
            'total'=>number_format((float)$this->total, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'status'=>ucwords($this->status),
            'order_code'=>$this->order_code,
        ];
        return parent::toArray($request);
    }
}
