<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class Item extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $r_image="storage/items/r_".$this->image;
        if(!file_exists($r_image)){
            $r_image="storage/items/".$this->image;
        }
        return [
            'id'=>$this->id,
            'name'=>ucwords($this->name),
            'image'=>strpos($this->image,"placeholder.com")?$this->image:asset("storage/items/".$this->image),
            'r_image'=>strpos($this->image,"placeholder.com")?$this->image:asset($r_image),
            'category_id'=>$this->category->id,
            'tax'=>$this->tax?"yes":"no",
            'tax_boolean'=>$this->tax?true:false,
            'visible'=>$this->visible?"yes":"no",
            'visible_boolean'=>$this->visible?true:false,
            "added"=> Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y, hh:mm:ss A')
        ];
        return parent::toArray($request);
    }
}
