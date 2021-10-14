<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Party;
use Validator;
use App\Http\Resources\Party as PartyResource;
use App\Http\Controllers\API\RegisterController;
use DB;
class PartyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $parties = Party::with('user');
        $count=$parties->get()->count();
        if($request->get('filter')){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->id)){
                $parties=$parties->whereIn('id',$filter->id);
            }
            if(isset($filter->name)){
                $parties=$parties->where('business_name','like',"%".strtolower($filter->name)."%");
            }
            $count=$parties->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $parties = $parties->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $parties=$parties->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(PartyResource::collection($parties->get()), 'Partys retrieved successfully.',$count);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function purchase_items(Request $request)
    {
        $parties = Party::with('user');
        $count=$parties->get()->count();
        if($request->get('filter')){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->id)){
                $parties=$parties->whereIn('id',$filter->id);
            }
            if(isset($filter->name)){
                $parties=$parties->where('business_name','like',"%".strtolower($filter->name)."%");
            }
            $count=$parties->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $parties = $parties->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $parties=$parties->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(PartyResource::collection($parties->get()), 'Partys retrieved successfully.',$count);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return (new RegisterController())->register($request);
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $party = Party::find($id);
  
        if (is_null($party)) {
            return $this->sendError('Party not found.');
        }
   
        return $this->sendResponse(new PartyResource($party), 'Party retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Party $party)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|unique:parties,business_name,'.$party->id,
            'address' => 'required',
            'contact_number' => 'required|unique:parties,contact_number,'.$party->id,
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        /*Extra Flieds*/
        $party->active=isset($input['active'])&&$input['active']=="yes"?1:0;
        $party->business_name=strtolower($input['business_name']);
        $party->address=strtolower($input['address']);
        $party->contact_number=$input['contact_number'];
        $party->save();
        return $this->sendResponse(new PartyResource($party), 'Party updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Party $party)
    {
        DB::table('users')->where('id',$party->user_id)->delete();
        $party->delete();
        return $this->sendResponse([], 'Party deleted successfully.');
    }
}
