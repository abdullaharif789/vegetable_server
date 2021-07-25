<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Item;
use Validator;
use App\Http\Resources\Item as ItemResource;
use File;
class ItemController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Item::with('category')->orderby('id',"DESC")->get();
        return $this->sendResponse(ItemResource::collection($items), 'Items retrieved successfully.');
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
            'name' => 'required|unique:items',
            'category_id' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $input['name']=strtolower($input['name']);
        $input['tax']=isset($input['tax'])&&$input['tax']=="yes"?1:0;
        $input['image']="https://via.placeholder.com/800/000000/FFF?text=".ucwords($input['name']);
        $item = Item::create($input);
        return $this->sendResponse(new ItemResource($item), 'Item created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Item::find($id);
        if (is_null($item)) {
            return $this->sendError('Item not found.');
        }
        return $this->sendResponse(new ItemResource($item), 'Item retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|unique:items,name,'.$item->id,
            'category_id' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $item->name = $input['name'];
        $item->tax=isset($input['tax'])&&$input['tax']=="yes"?1:0;
        $item->category_id = $input['category_id'];
        $item->save();
        return $this->sendResponse(new ItemResource($item), 'Item updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        $item->delete();
   
        return $this->sendResponse([], 'Item deleted successfully.');
    }
}
