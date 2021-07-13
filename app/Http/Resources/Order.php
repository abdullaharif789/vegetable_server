<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id'=>$this->id,
            'party'=>[
                'name'=>ucwords($this->party->name),
            ],
            'cart'=>json_decode($this->cart),
            'total'=>$this->total,
        ];
        return parent::toArray($request);
    }
}
