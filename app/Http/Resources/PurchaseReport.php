<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReport extends JsonResource
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
            $item->tax=0;
            $totalQuantity+=$item->quantity;
            $item->profit=($item->price-$item->cost_price) * $item->quantity;
            $item->total_with_tax=number_format($item->quantity*$item->total, 2, '.', '');
            $totalProfit+=$item->profit;
            $item->profit=number_format((float)$item->profit, 2, '.', '');
        }
        return [
            'id'=>$this->id,
            'party_id'=>$this->party_id,
            'party_business_name'=>ucwords($this->party->business_name),
            'cart'=>$cart,
            'total_items'=>count($cart),
            'total'=>number_format((float)$this->total, 2, '.', ''),
            'total_tax'=>number_format((float)$this->total_tax, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'total_profit'=>number_format((float)$totalProfit, 2, '.', ''),
            'created_at'=>$this->created_at,
        ];
        return parent::toArray($request);
    }
}
