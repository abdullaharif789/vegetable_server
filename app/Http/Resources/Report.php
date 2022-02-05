<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            $item->total_with_tax=number_format($item->quantity*$item->total+$item->tax, 2, '.', '');
            $totalProfit+=$item->profit;
            $item->profit=number_format((float)$item->profit, 2, '.', '');
        }
        return [
            'id'=>$this->id,
            'order_code'=>$this->order_code,
            'party_id'=>$this->party_id,
            'party_business_name'=>ucwords($this->party->business_name),
            'cart'=>$cart,
            'total_items'=>count($cart),
            'total'=>number_format((float)$this->total, 2, '.', ''),
            'total_tax'=>number_format((float)$this->total_tax, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'total_profit'=>number_format((float)$totalProfit, 2, '.', ''),
            'status'=>ucwords($this->status),
            'order_code'=>$this->order_code,
            'created_at'=>Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y, hh:mm:ss A'),
        ];
        return parent::toArray($request);
    }
}