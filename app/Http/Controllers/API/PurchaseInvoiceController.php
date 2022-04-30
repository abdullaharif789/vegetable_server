<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SellCostPrice;
use App\Models\Transaction;
use Validator;
use DB;
use App;
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
        $route=\Request::route()->getName();
        $purchaseInvoices = PurchaseInvoice::with("party")->where("status","active");
        $count = $purchaseInvoices->get()->count();
        $filterFound=false;
        if($request->get("filter")){
            $filter=json_decode($request->get("filter"));
            if($route != "daily_invoice_reports"){
                if(isset($filter->van))
                {
                    $filterFound=true;
                    $purchaseInvoices=$purchaseInvoices->where('van_id',$filter->van);
                }
            }
            else{
                if(isset($filter->van))
                    $purchaseInvoices=$purchaseInvoices->where('van_id',$filter->van);
            }
            if(isset($filter->party_id))
                $purchaseInvoices=$purchaseInvoices->where('party_id',$filter->party_id);

            if(isset($filter->item_id))
                $purchaseInvoices=$purchaseInvoices->whereJsonContains('cart', [['item_id' => $filter->item_id]]);
            if($route != "daily_invoice_reports"){
                if(isset($filter->start_date) || isset($filter->end_date)){
                    $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                    $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                    $purchaseInvoices=$purchaseInvoices->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
                }
            }else{
                if(isset($filter->current_date)){
                    $filterFound=true;
                    $purchaseInvoices=$purchaseInvoices->whereDate('created_at',$filter->current_date);
                }
            }

            $count = $purchaseInvoices->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $purchaseInvoices = $purchaseInvoices->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $purchaseInvoices=$purchaseInvoices->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        if($filterFound==false && $route == "daily_invoice_reports"){
            return $this->sendResponse(PurchaseInvoiceResource::collection([]), 'Purchase Invoices retrieved successfully.',0);
        }
        return $this->sendResponse(PurchaseInvoiceResource::collection($purchaseInvoices->get()), 'Purchase Invoices retrieved successfully.',$count);
    }
    public function send_invoice_email(Request $request){
        $input = $request->all();

        $message = $input['invoice_message'];

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($message);
        $invoice = $pdf->stream('invoice.pdf');
        $content = chunk_split(base64_encode($invoice));

        $separator = md5(time());
        $eol = "\r\n";

        $to = $input['email'] ? $input['email'] : "abdullaharif789@gmail.com";
        $subject="Invoice from EveryDayFreshFood";
        $from = "invoices@everydayfreshfood.com";

        $headers = "From: EverDay Fresh Food Invoice ". $from . $eol;
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
        $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
        $headers .= "This is a MIME encoded message." . $eol;

        // message
        $body = "--" . $separator . $eol;
        $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
        $body .= "Content-Transfer-Encoding: 8bit" . $eol;
        $body .= $message . $eol;

        // attachment
        $body .= "--" . $separator . $eol;
        $body .= "Content-Type: application/octet-stream; name=\"" . "invoice.pdf" . "\"" . $eol;
        $body .= "Content-Transfer-Encoding: base64" . $eol;
        $body .= "Content-Disposition: attachment" . $eol;
        $body .= $content . $eol;
        $body .= "--" . $separator . "--";

        if(mail($to,$subject,$message,$headers)){
            return $this->sendResponse('Invoice sent successfully.',null);
        }
        else{
            return $this->sendResponse('Sorry, Invoice sent unsuccessfully. Please try later.',null);
        }
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
                // if($oldPurchaseInvoice==null){
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
                // }else
                if($oldPurchaseInvoice!=null){
                    foreach ($oldPurchaseInvoice->cart as $key_new => $value_new){
                        if($value_new->type == $value->type && $value_new->item_id == $value->item_id){
                           $sellCostPrice['price']=(float)$value_new->price;
                        }
                    }
                }
                $value->cost_price=number_format($sellCostPrice['cost_price'], 2, '.', '');
                $value->price=number_format($sellCostPrice['price'], 2, '.', '');
                $tempTot = (float)$sellCostPrice['price'] * (float)$value->quantity;
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
            if(isset($filter->van))
                $reports=$reports->where('van_id',$filter->van);
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
        DB::beginTransaction();
        try {
            DB::table('purchase_invoices')->where("purchase_order_id",$input['purchase_order_id'])->update(["status"=>"inactive"]);
            $oldInvoice=PurchaseInvoice::where("purchase_order_id",$input['purchase_order_id'])->orderBy('id','DESC')->first();
            $current = date('Y-m-d H:i:s');
            $input['created_at']=$current;
            $input['updated_at']=$current;
            if($oldInvoice!=null){
                Transaction::where("purchase_invoice_id",$oldInvoice->id)->delete();
                $input['created_at']=$oldInvoice->created_at;
                $input['updated_at']=$oldInvoice->created_at;
            }
            DB::table('purchase_invoices')->insert($input);
            $purchaseOrder=PurchaseInvoice::orderBy("id","DESC")->first();

            // ///// Create Transaction
            $transaction['party_id']=$input['party_id'];
            $transaction['amount']=(float)($input['total'] - $input['total'] * $input['discount']);
            $transaction['purchase_invoice_id']=$purchaseOrder->id;
            $transaction['created_at']=$current;
            $transaction['updated_at']=$current;
            $transaction['date']=$current;
            DB::table('transactions')->insert($transaction);
            DB::commit();
            return $this->sendResponse(new PurchaseInvoiceResource($purchaseOrder), 'Purchase Invoice created successfully.');
        }
        catch (\Exception $e) {
            DB::rollback();
            return $this->sendError("Some error occured while creating purchase invoice and transaction.");
        }
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
        // $input = $request->all();
        // $purchaseInvoice->save();

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
