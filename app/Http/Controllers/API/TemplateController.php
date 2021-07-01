<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Template;
use Validator;
use App\Http\Resources\Template as TemplateResource;
   
class TemplateController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = Template::all();
    
        return $this->sendResponse(TemplateResource::collection($templates), 'Templates retrieved successfully.');
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
        /*
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        */
        $template = Template::create($input);
   
        return $this->sendResponse(new TemplateResource($template), 'Template created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = Template::find($id);
  
        if (is_null($template)) {
            return $this->sendError('Template not found.');
        }
   
        return $this->sendResponse(new TemplateResource($template), 'Template retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Template $template)
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
        //$template->name = $input['name'];
        //$template->detail = $input['detail'];
        $template->save();
   
        return $this->sendResponse(new TemplateResource($template), 'Template updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Template $template)
    {
        $template->delete();
   
        return $this->sendResponse([], 'Template deleted successfully.');
    }
}
