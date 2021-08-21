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
    public function index(Request $request)
    {
        $items = Item::with('category');
        if($request->get("filter")){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->name))
                $items=$items->where('name','like',"%".strtolower($filter->name)."%");
        }
        return $this->sendResponse(ItemResource::collection($items->orderby('name',"ASC")->get()), 'Items retrieved successfully.');
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
            'category_id' => 'required',
            'image'=> 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        //Copy Image
        $image = $request->image;
        list($type, $image) = explode(';', $image);
        list(, $image)      = explode(',', $image);
        $image = base64_decode($image);
        $type=explode("/",$type)[1];
        $newName=uniqid().".".$type;
        file_put_contents('storage/items/'.$newName, $image);
        //End Copy Image
        $input['name']=strtolower($input['name']);
        $input['tax']=isset($input['tax'])&&$input['tax']=="yes"?1:0;
        $input['visible']=isset($input['visible'])&&$input['visible']=="yes"?1:0;
        $input['image']=$newName;//"https://via.placeholder.com/800/000000/FFF?text=".ucwords($input['name']);
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
        if (!(strpos($request->image, 'storage') !== false)) {
            $image = $request->image;
            list($type, $image) = explode(';', $image);
            list(, $image)      = explode(',', $image);
            $image = base64_decode($image);
            $type=explode("/",$type)[1];
            $newName=uniqid().".".$type;
            unlink('storage/items/'.$item->image);
            file_put_contents('storage/items/'.$newName, $image);
            $item->image=$newName;
        }
        $item->name = $input['name'];
        $item->tax=isset($input['tax'])&&$input['tax']=="yes"?1:0;
        $item->visible=isset($input['visible'])&&$input['visible']=="yes"?1:0;
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
