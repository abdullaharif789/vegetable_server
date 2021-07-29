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
        $tax=20.00;
         return [
            "id"=> $this->id,
            "item_id"=> $this->item_id,
            "title"=> ucwords($this->item->title),
            'unit'=> $this->unit,
            'remaining_unit'=> $this->remaining_unit,
            'buying_price'=>  $this->buying_price,
            'selling_price'=>  $this->selling_price,
            'tax'=> $this->item->tax?$this->selling_price/100*$tax:0,
            "date"=> $this->stock_date,
            'image'=>$this->item->image,
            'title'=>ucwords($this->item->name),
            'active'=>$this->active,
            'tax_available'=>$this->item->tax?true:false
        ];
        return parent::toArray($request);
    }
}
// {
//   id: 0,
//   uri: "https://picsum.photos/100",
//   title: "Brown eggs",
//   description: "Raw organic brown eggs in a basket.",
//   price: 120,
//   quantity: 0,
// },