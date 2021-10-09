<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\PurchaseOrder;
use Validator;
use App\Http\Resources\PurchaseOrder as PurchaseOrderResource;
   
class PurchaseOrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::where("id",">","0");
        $count = $purchaseOrders->get()->count();;
        if($request->get("filter")){
            $filter=json_decode($request->get("filter"));

            if(isset($filter->van))
                $purchaseOrders=$purchaseOrders->where('van_id',$filter->van);

            if(isset($filter->party_id))
                $purchaseOrders=$purchaseOrders->where('party_id',$filter->party_id);

            if(isset($filter->start_date) || isset($filter->end_date)){
                $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                $purchaseOrders=$purchaseOrders->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
            }

            $count = $purchaseOrders->get()->count();;
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $purchaseOrders = $purchaseOrders->orderBy($sort[0],$sort[1]);
        }

        return $this->sendResponse(PurchaseOrderResource::collection($purchaseOrders->get()), 'Purchase Orders retrieved successfully.',$count);
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
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $input['cart']=json_encode($input['cart']);
        $purchaseOrder = PurchaseOrder::create($input);
        return $this->sendResponse(new PurchaseOrderResource($purchaseOrder), 'Purchase Order created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (is_null($purchaseOrder)) {
            return $this->sendError('PurchaseOrder not found.');
        }
        return $this->sendResponse(new PurchaseOrderResource($purchaseOrder), 'PurchaseOrder retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
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
        //$purchaseOrder->name = $input['name'];
        //$purchaseOrder->detail = $input['detail'];
        $purchaseOrder->save();
   
        return $this->sendResponse(new PurchaseOrderResource($purchaseOrder), 'PurchaseOrder updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return $this->sendResponse([], 'PurchaseOrder deleted successfully.');
    }
}
