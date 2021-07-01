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
        $items = Item::get();
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
        return $this->sendResponse($input,'');
        $validator = Validator::make($input, [
            'name' => 'required',
            'image' => 'required|image'
        ]);
        //$input['image']=$input['image']['image'];
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $input['name']=strtolower($input['name']);
        //File::move(public_path($input['image']),public_path('/storage/vegetables/'));
        return $this->sendResponse($input,'');
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
        /*
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        */
        //$item->name = $input['name'];
        //$item->detail = $input['detail'];
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
