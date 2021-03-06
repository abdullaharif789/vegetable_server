<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Expense extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            'extra'=>$this->extra,
            'expense_type'=>$this->expense_type,
            'expense_type_id'=>$this->expense_type->id,
            'amount'=>(float)$this->amount,
            "date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "created_at"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
        ];
    }
}
