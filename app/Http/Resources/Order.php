<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'total_tax'=>number_format((float)$this->total_tax, 2, '.', ''),
            'total_quantity'=>$totalQuantity,
            'status'=>ucwords($this->status),
            'order_code'=>$this->order_code,
            'order_from'=>$this->manual?'Manual':"App",
            'created_at'=>Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y, hh:mm:ss A')
        ];
        return parent::toArray($request);
    }
}
