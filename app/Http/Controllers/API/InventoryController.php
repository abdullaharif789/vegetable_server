<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Inventory;
use Validator;
use App\Http\Resources\Inventory as InventoryResource;
use DB;
class InventoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return json_decode($request->get("range"));
        $inventories=Inventory::with('item');
        $count=$inventories->get()->count();
        if($request->get("item_id"))
            $inventories = $inventories->where("item_id",$request->get("item_id"));
        else if($request->get("source")=="app"){
            $inventories=$inventories->where('remaining_unit','>',0)->whereIn('id', function($query) {
               $query->from('inventories')->groupBy('item_id')->selectRaw('MAX(id)');
            })->orderby('buying_price','asc')->get();
            $newInventories=array();
            foreach($inventories as $inventory)
                if($inventory['item']->visible==1)
                    array_push($newInventories, $inventory);
            return $this->sendResponse(InventoryResource::collection($newInventories), 'Inventories retrieved successfully.');
        }
        else if($request->get("available")=="true"){
            $inventories=$inventories->where('remaining_unit','>',0);
        }
        else{
            if($request->get("filter")){
                $filter=json_decode($request->get("filter"));
                if(isset($filter->order_code))
                    $inventories=$inventories->where('order_code','like',"%".strtoupper($filter->order_code)."%");
                if(isset($filter->created_at))
                    $inventories=$inventories->whereDate('stock_date',$filter->created_at);
                if(isset($filter->start_date) || isset($filter->end_date)){
                    $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                    $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                    $inventories=$inventories->whereDate('created_at','<=',$to)->whereDate('created_at','>=',$from);
                }
                if(isset($filter->status))
                    $inventories=$inventories->where('status','like',strtolower($filter->status));
                if(isset($filter->item_id))
                    $inventories=$inventories->where('item_id',$filter->item_id);
                $count=$inventories->get()->count();
            }
            if($request->get("sort")){
                $sort=json_decode($request->get("sort"));
                $inventories = $inventories->orderBy($sort[0],$sort[1]);
            }
            if($request->get("range")){
                $range=json_decode($request->get("range"));
                $inventories=$inventories->offset($range[0])->limit($range[1]-$range[0]+1);
            }
        }
        //Manage Active App Chips
        $inventories=$inventories->get();
        $tempIds=[];
        $activeIds=Inventory::with('item')->whereIn('id', function($query) {
               $query->from('inventories')->groupBy('item_id')->selectRaw('MAX(id)');
            })->select('id')->get();
        for($i=0;$i<count($inventories);$i++){
            if($this->arraySearch($inventories[$i]->id,$activeIds)) $inventories[$i]->active=true;
            else $inventories[$i]->active=false;
        }
        return $this->sendResponse(InventoryResource::collection($inventories), 'Inventories retrieved successfully.',$count);
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
            'item_id' => 'required',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'unit' => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input['remaining_unit']=$input['unit'];
        $inventory = Inventory::create($input);
        return $this->sendResponse(new InventoryResource($inventory), 'Inventory created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inventory = Inventory::find($id);

        if (is_null($inventory)) {
            return $this->sendError('Inventory not found.');
        }

        return $this->sendResponse(new InventoryResource($inventory), 'Inventory retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inventory $inventory)
    {
        $input = $request->all();
        $inventory->selling_price=(float)$input['selling_price'];
        $inventory->unit=(integer)$input['unit'];
        $inventory->item_id=$input['item_id'];
        $inventory->save();
        return $this->sendResponse(new InventoryResource($inventory), 'Inventory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return $this->sendResponse([], 'Inventory deleted successfully.');
    }
}
