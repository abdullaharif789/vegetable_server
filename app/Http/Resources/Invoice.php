<?php

namespace App\Http\Resources;
use App\Models\Party;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class Invoice extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $party=Party::with("user")->where('id',$this->order->party_id)->first();
        $cart=json_decode($this->order->cart);
        $totalQuantity=0;
        foreach($cart as $item){
            $totalQuantity+=$item->quantity;
        }
        return [
           'id'=> $this->id,
           'order_id'=> $this->order_id,
           'created_at'=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y, hh:mm:ss A'),
           'order'=>[
                'cart' => $cart,
                'status' => $this->order->status,
                'order_code'=>$this->order->order_code,
                'total'=>number_format((float)$this->order->total, 2, '.', ''),
                'total_tax'=>number_format((float)$this->order->total_tax, 2, '.', ''),
                'party_id'=>$this->order->party_id,
                'total_items'=>count($cart),
                'total_quantity'=>$totalQuantity,
           ],
           'party'=>[
                'name'=>ucwords($party->user->name),
                'business_name'=>ucwords($party->business_name),
                'address'=>ucwords($party->address),
                'contact_number'=>ucwords($party->contact_number),
           ],
           'bank_visible'=>$this->order->bank

        ];
        return parent::toArray($request);
    }
}
/*
BusinessName
Name
Address
Contact
*/