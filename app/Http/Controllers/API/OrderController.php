<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use Validator;
use App\Http\Resources\Order as OrderResource;
   
class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->get("party_id"))
            $orders = Order::where('party_id',$request->get("party_id"))->orderBy('id','desc')->get();
        else
            $orders = Order::orderBy('id','desc')->get();
        return $this->sendResponse(OrderResource::collection($orders), 'Orders retrieved successfully.');
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
        /*
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        */
        //$order->name = $input['name'];
        //$order->detail = $input['detail'];
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
        $order->delete();
   
        return $this->sendResponse([], 'Order deleted successfully.');
    }
}
