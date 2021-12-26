<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Invoice;
use Validator;
use App\Http\Resources\Invoice as InvoiceResource;
   
class InvoiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with('order');
         if($request->get("filter")){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->order_code))
                $invoices=$invoices->where('order_code','like',"%".strtoupper($filter->order_code)."%");
            if(isset($filter->start_date) || isset($filter->end_date)){
                $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                $invoices=$invoices->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
            }
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $invoices = $invoices->orderBy($sort[0],$sort[1]);
        }
        return $this->sendResponse(InvoiceResource::collection($invoices->get()), 'Invoices retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        
        $validator = Validator::make($input, [
            'order_id' => 'required',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $invoice = Invoice::create($input);
        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::find($id);
  
        if (is_null($invoice)) {
            return $this->sendError('Invoice not found.');
        }
   
        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        $input = $request->all();
        $invoice->save();
        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
   
        return $this->sendResponse([], 'Invoice deleted successfully.');
    }
}
