<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
use Validator;
use App\Http\Resources\Category as CategoryResource;
   
class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::where("id",">",0);
        $count=$categories->get()->count();
        if($request->get('filter')){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->name)){
                $categories=$categories->where('name','like',"%".strtolower($filter->name)."%");
            }
            $count=$categories->get()->count();
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $categories=$categories->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(CategoryResource::collection($categories->get()), 'Categorys retrieved successfully.',$count);
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
        $input['name']=strtolower($input['name']);
        $validator = Validator::make($input, [
            'name' => 'required|unique:categories',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $category = Category::create($input);
        return $this->sendResponse(new CategoryResource($category), 'Category created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);
  
        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }
   
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $input = $request->all();
        $input['name']=strtolower($input['name']);
        $validator = Validator::make($input, [
            'name' => 'required|unique:categories,name,'.$category->id,
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $category->name=$input['name'];
        $category->save();
        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->sendResponse([], 'Category deleted successfully.');
    }
}
