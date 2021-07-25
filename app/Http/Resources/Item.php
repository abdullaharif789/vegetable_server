<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id'=>$this->id,
            'name'=>ucwords($this->name),
            'image'=>$this->image,
            "added"=> $this->created_at,
            'category_id'=>$this->category->id,
            'tax'=>$this->tax?"yes":"no",
            'tax_boolean'=>$this->tax?true:false
        ];
        return parent::toArray($request);
    }
}
