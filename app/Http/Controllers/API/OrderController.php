<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use App\Models\Invoice;
use Validator;
use App\Http\Resources\Order as OrderResource;
use DB;   
class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders=Order::with('party');
        if($request->get("party_id"))
            $orders = Order::where('party_id',$request->get("party_id"))->orderBy('id','desc');
        else{
            if($request->get("filter")){
                $filter=json_decode($request->get("filter"));
                if(isset($filter->order_code))
                    $orders=$orders->where('order_code','like',"%".strtoupper($filter->order_code)."%");
                if(isset($filter->created_at))
                    $orders=$orders->whereDate('created_at',$filter->created_at);
                if(isset($filter->status))
                    $orders=$orders->where('status','like',strtolower($filter->status));
            }
            if($request->get("sort")){
                $sort=json_decode($request->get("sort"));
                $orders = $orders->orderBy($sort[0],$sort[1]);
            }
        }
        return $this->sendResponse(OrderResource::collection($orders->get()), 'Orders retrieved successfully.');
    }
    public function reports()
    {
        $orders=Order::with('party');
        return $this->sendResponse(OrderResource::collection($orders->get()), 'Orders retrieved successfully.');
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
            'total' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $input['order_code']=strtoupper(uniqid());
        $order = Order::create($input);
        return $this->sendResponse(new OrderResource($order), 'Order created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);
  
        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }
   
        return $this->sendResponse(new OrderResource($order), 'Order retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'status' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $order->status = strtolower($input['status']);
        // Here we create invoice after order is completed
        if(strtolower($input['status'])=="completed"){
            $input['order_id']=$order->id;
            $input['order_code']=strtoupper($order->order_code);
            $invoice = Invoice::create($input);
        }
        $order->save();
        return $this->sendResponse(new OrderResource($order), 'Order updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        DB::table("orders")->where('id',$order->id)->update(['status'=>'cancelled']);
        return $this->sendResponse([], 'Order deleted successfully.');
    }
    
}
