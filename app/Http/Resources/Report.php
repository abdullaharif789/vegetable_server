<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Report extends JsonResource
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
        $totalProfit=0;
        foreach($cart as $item){
            $totalQuantity+=$item->quantity;
            $item->profit=($item->price-$item->buying_price) * $item->quantity;
            $totalProfit+=$item->profit;
            $item->profit=number_format((float)$item->profit, 2, '.', '');
        }
        return [
            'id'=>$this->id,
            'party_id'=>$this->party_id,
            'cart'=>$cart,
            'total_items'=>count($cart),
            'total'=>number_format((float)$this->total, 2, '.', ''),
            'total_tax'=>number_format((float)$this->total_tax, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'total_profit'=>number_format((float)$totalProfit, 2, '.', ''),
            'status'=>ucwords($this->status),
            'order_code'=>$this->order_code,
            'created_at'=>$this->created_at,
        ];
        return parent::toArray($request);
    }
}
