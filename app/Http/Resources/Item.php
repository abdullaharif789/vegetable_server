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
            "added"=> Date('d-M-Y',strtotime($this->created_at)),
        ];
        return parent::toArray($request);
    }
}
