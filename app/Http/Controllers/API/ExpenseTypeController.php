<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\ExpenseType;
use Validator;
use App\Http\Resources\ExpenseType as ExpenseTypeResource;

class ExpenseTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expenseTypes = ExpenseType::where("id",">",0);
        $count=$expenseTypes->get()->count();
        if($request->get('filter')){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->type)){
                $expenseTypes=$expenseTypes->where('type','like',"%".strtolower($filter->type)."%");
            }
            $count=$expenseTypes->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $expenseTypes = $expenseTypes->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $expenseTypes=$expenseTypes->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(ExpenseTypeResource::collection($expenseTypes->get()), 'ExpenseTypes retrieved successfully.',$count);
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
            'name' => 'required',
            'default_amount' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $expenseType = ExpenseType::create($input);
        return $this->sendResponse(($expenseType), 'ExpenseType created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expenseType = ExpenseType::find($id);

        if (is_null($expenseType)) {
            return $this->sendError('ExpenseType not found.');
        }

        return $this->sendResponse(new ExpenseTypeResource($expenseType), 'ExpenseType retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpenseType $expenseType)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'default_amount' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $expenseType->name=$input['name'];
        $expenseType->default_amount=$input['default_amount'];
        $expenseType->save();
        return $this->sendResponse(($expenseType), 'ExpenseType updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseType $expenseType)
    {
        $expenseType->delete();
        return $this->sendResponse([], 'ExpenseType deleted successfully.');
    }
}
