<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SellCostPrice;
use App\Models\Transaction;
use Validator;
use App\Http\Resources\PurchaseInvoice as PurchaseInvoiceResource;
use App\Http\Resources\PurchaseOrder as PurchaseOrderResource;
   
class PurchaseInvoiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::with("party");
        $count = $purchaseInvoices->get()->count();
        if($request->get("filter")){
            $filter=json_decode($request->get("filter"));

            if(isset($filter->van))
                $purchaseInvoices=$purchaseInvoices->where('van_id',$filter->van);

            if(isset($filter->party_id))
                $purchaseInvoices=$purchaseInvoices->where('party_id',$filter->party_id);

            if(isset($filter->item_id))
                $purchaseInvoices=$purchaseInvoices->whereJsonContains('cart', [['item_id' => $filter->item_id]]);

            if(isset($filter->start_date) || isset($filter->end_date)){
                $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                $purchaseInvoices=$purchaseInvoices->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
            }

            $count = $purchaseInvoices->get()->count();;
        }
        return $this->sendResponse(PurchaseOrderResource::collection($purchaseInvoices->get()), 'Purchase Invoices retrieved successfully.');
    }
    public function revised_purchase_orders(Request $request)
    {
        $purchaseInvoices = PurchaseOrder::with("party")->where('id',$request->purchase_order_id)->get();
        $purchaseInvoices = $purchaseInvoices->each(function ($purchaseInvoice, $index){
            $purchaseInvoice->cart=json_decode($purchaseInvoice->cart);
            $purchaseInvoice->total = 0.00;
            foreach ($purchaseInvoice->cart as $key => $value) {
                $sellCostPrice=SellCostPrice::where([
                    ['item_id','=',$value->item_id],
                    ['item_type','=',$value->type],
                ])->orderBy('id', 'DESC')->first();
                if(!isset($sellCostPrice)){
                    $sellCostPrice=array(
                        "cost_price"=>0.00,
                        "price"=>0.00,
                    );
                }
                $value->cost_price=number_format($sellCostPrice['cost_price'], 2, '.', '');
                $value->price=number_format($sellCostPrice['price'], 2, '.', '');
                $tempTot = $sellCostPrice['price'] * $value->quantity;
                $purchaseInvoice->total += $tempTot;
                $value->total=number_format($tempTot, 2, '.', '');
            }
            $purchaseInvoice->cart=json_encode($purchaseInvoice->cart);
        });
        return $this->sendResponse(PurchaseOrderResource::collection($purchaseInvoices), 'Purchase Invoices retrieved successfully.');
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
           'party_id' => 'required',
           'cart' => 'required',
           'van_id' => 'required',
           'total' => 'required',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        /// Add Sell and cost updated prices
        foreach ($input['cart'] as $key => $value) {
            SellCostPrice::where([
                ["item_id","=",$value['item_id']],
                ["item_type","=",$value['type']],
            ])->updateOrCreate(["cost_price"=>$value['cost_price'],"price"=>$value['price'],"item_id"=>$value['item_id'],"item_type"=>$value['type']]);
        }
        $input['cart']=json_encode($input['cart']);
        $purchaseOrder = PurchaseInvoice::create($input);
        // ///// Create Transaction
        $input['amount']=(float)($input['total']);
        $input['party_id']=$input['party_id'];
        Transaction::create($input);
        return $this->sendResponse(new PurchaseOrderResource($purchaseOrder), 'Purchase Invoice created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchaseInvoice = PurchaseInvoice::find($id);
  
        if (is_null($purchaseInvoice)) {
            return $this->sendError('PurchaseInvoice not found.');
        }
   
        return $this->sendResponse(new PurchaseInvoiceResource($purchaseInvoice), 'Purchase Invoice retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $input = $request->all();
        $purchaseInvoice->save();
   
        return $this->sendResponse(new PurchaseInvoiceResource($purchaseInvoice), 'Purchase Invoice updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->delete();
        return $this->sendResponse([], 'Purchase Invoice deleted successfully.');
    }
}
