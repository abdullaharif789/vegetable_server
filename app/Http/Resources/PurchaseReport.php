<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
        $total=0;
        foreach($cart as $item){
            $item->tax=0;
            $totalQuantity+=$item->quantity;
            $item->cost_price=$item->cost_price?(float)$item->cost_price:0.00;
            $item->price=$item->price?(float)$item->price:0.00;
            $item->profit=($item->price - $item->cost_price) * (float)$item->quantity;
            $item->total_with_tax=number_format((float)$item->quantity*$item->tax + $item->total, 2, '.', '');
            $totalProfit+=$item->profit;
            $item->profit=number_format((float)$item->profit, 2, '.', '');
            $item->cost_price=number_format($item->cost_price, 2, '.', '');
            $total+=(float)$item->total;
        }
        $this->total=$total;
        return [
            'id'=>$this->id,
            'party_id'=>$this->party_id,
            'party_business_name'=>ucwords($this->party->business_name),
            'cart'=>$cart,
            'total_items'=>count($cart),
            "discount"=> ($this->discount * 100)."%",
            "discount_amount"=>number_format($this->total*$this->discount, 2, '.', ''),
            "total"=> number_format($this->total - $this->total*$this->discount, 2, '.', ''),
            "total_without_discount"=> number_format($this->total, 2, '.', ''),
            'total_tax'=>number_format((float)$this->total_tax, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'profit'=>number_format((float)$totalProfit, 2, '.', ''),
            'total_profit'=>number_format((float)$totalProfit - $this->total*$this->discount, 2, '.', ''),
            'created_at'=>Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
        ];
        return parent::toArray($request);
    }
}
