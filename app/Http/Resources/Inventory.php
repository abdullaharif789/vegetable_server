<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;
use Carbon\Carbon;
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
        $r_image="storage/items/r_".$this->item->image;
        if(!file_exists($r_image)){
            $r_image="storage/items/".$this->item->image;
        }
        $tax=20.00;
        $category_title=DB::table("categories")->select('name')->where('id',$this->item->category_id)->first()->name;
        return [
            "id"=> $this->id,
            "item_id"=> $this->item_id,
            "title"=> ucwords($this->item->title),
            'unit'=> $this->unit,
            'remaining_unit'=> $this->remaining_unit,
            'buying_price'=>  $this->buying_price,
            'selling_price'=>  $this->selling_price,
            'tax'=> $this->item->tax?$this->selling_price/100*$tax:0,
            "date"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y, hh:mm:ss A'),
            'image'=>strpos($this->item->image,"placeholder.com")?$this->item->image:asset("storage/items/".$this->item->image),
            'r_image'=>strpos($this->item->image,"placeholder.com")?$this->item->image:asset($r_image),
            'title'=>ucwords($this->item->name),
            'active'=>$this->active,
            'tax_available'=>$this->item->tax?true:false,
            'category_id'=>$this->item->category_id,
            'category_title'=>ucwords($category_title),
            'visible'=>$this->item->visible
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
