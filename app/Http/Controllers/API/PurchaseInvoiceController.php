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
use App\Http\Resources\PurchaseReport as ReportResource;
   
class PurchaseInvoiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::with("party")->where("status","active");
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

            $count = $purchaseInvoices->get()->count();
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $purchaseInvoices=$purchaseInvoices->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(PurchaseInvoiceResource::collection($purchaseInvoices->get()), 'Purchase Invoices retrieved successfully.',$count);
    }
    public function revised_purchase_orders(Request $request)
    {
        $purchaseInvoices = PurchaseOrder::with("party")->where('id',$request->purchase_order_id)->get();
        $oldPurchaseInvoice=PurchaseInvoice::where("purchase_order_id",$request->purchase_order_id)->orderBy("id","DESC")->first();
        if($oldPurchaseInvoice!=null){
            $oldPurchaseInvoice->cart=json_decode($oldPurchaseInvoice->cart);
        }
        $purchaseInvoices = $purchaseInvoices->each(function ($purchaseInvoice, $index) use($oldPurchaseInvoice){
            $purchaseInvoice->cart=json_decode($purchaseInvoice->cart);
            $purchaseInvoice->total = 0.00;
            foreach ($purchaseInvoice->cart as $key => $value) {
                 $sellCostPrice=array(
                            "cost_price"=>0.00,
                            "price"=>0.00,
                        );
                if($oldPurchaseInvoice==null){
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
                }else{
                    foreach ($oldPurchaseInvoice->cart as $key_new => $value_new){
                        if($value_new->type == $value->type && $value_new->item_id == $value->item_id){
                           $sellCostPrice=array(
                                "cost_price"=>(float)$value_new->cost_price,
                                "price"=>(float)$value_new->price,
                            ); 
                        }
                    }
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
    public function purchase_order_reports(Request $request)
    {
        $reports=PurchaseInvoice::with('party')->where("status","active");
        $count = $reports->get()->count();
        if($request->get("filter")){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->order_code))
                $reports=$reports->where('order_code','like',"%".strtoupper($filter->order_code)."%");
            if(isset($filter->start_date) || isset($filter->end_date)){
                $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                $reports=$reports->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
            }
            if(isset($filter->party_id))
                $reports=$reports->where('party_id',$filter->party_id);
            $count = $reports->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $reports = $reports->orderBy($sort[0],$sort[1]);
        }
        // return $request->get("range");
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $reports=$reports->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        $reports=$reports->get();
        $reports=ReportResource::collection($reports);
        return $this->sendResponse($reports, 'Orders retrieved successfully.',$count);
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
        $input['bank']=isset($input['bank'])&&$input['bank']=="Yes"?true:false;
        // Inactive previous if available
        PurchaseInvoice::where("purchase_order_id",$input['purchase_order_id'])->update(["status"=>"inactive"]);
        $oldInvoice=PurchaseInvoice::where("purchase_order_id",$input['purchase_order_id'])->orderBy('id','DESC')->first();
        if($oldInvoice!=null)
            Transaction::where("purchase_invoice_id",$oldInvoice->id)->delete();
        $purchaseOrder = PurchaseInvoice::create($input);
        // ///// Create Transaction
        $input['purchase_invoice_id']=$purchaseOrder->id;
        $input['amount']=(float)($input['total']);
        $input['party_id']=$input['party_id'];
        Transaction::create($input);
        return $this->sendResponse(new PurchaseInvoiceResource($purchaseOrder), 'Purchase Invoice created successfully.');
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
        Transaction::where("purchase_invoice_id",$purchaseInvoice->id)->delete();
        $purchaseInvoice->delete();
        return $this->sendResponse([], 'Purchase Invoice deleted successfully.');
    }
}
