<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PurchaseInvoice;
use App\Http\Resources\PurchaseInvoice as PurchaseInvoiceResource;

class ETransaction extends JsonResource
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
            "id"=>$this->id,
            "party_id"=>$this->party_id,
            "party_name"=>$this->party?ucwords($this->party->business_name):null,
            "amount"=>number_format($this->amount, 2, '.', ','),
            "f_amount"=>round($this->amount, 2),
            'paid_boolean'=>$this->paid?true:false,
            'paid'=>$this->paid?"Paid":"Unpaid",
            "purchase_invoice_id"=>$this->purchase_invoice_id,
            "custom_purchase_invoice_id"=>$this->custom_purchase_invoice_id,
            "date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London')->isoFormat('DD/MM/Y'),
            "new_date"=>Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->setTimezone('Europe/London'),
        ];
        return parent::toArray($request);
    }
}
