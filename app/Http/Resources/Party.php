<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Party extends JsonResource
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
            'user_id'=>$this->user_id,
            'name'=>utf8_encode(ucwords($this->user->name)),
            'email'=>$this->user->email,
            'avatar'=>$this->avatar,
            'business_name'=>ucwords($this->business_name),
            'address'=>ucwords($this->address),
            'contact_number'=>ucwords($this->contact_number),
            'active_boolean'=>$this->active?true:false,
            'active'=>$this->active?"yes":"no",
        ];
    }
}
