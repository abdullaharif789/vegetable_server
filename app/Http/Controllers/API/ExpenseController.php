<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Expense;
use Validator;
use App\Http\Resources\Expense as ExpenseResource;

class ExpenseController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expenses = Expense::with("expense_type");
        $count=$expenses->get()->count();
        if($request->get('dates')){
            $dates=explode(",",$request->get('dates'));
            $expenses=$expenses->whereIn('date',$dates);
        }
        if($request->get('filter')){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->expense_type)){
                $expenses=$expenses->where('expense_type_id', $filter->expense_type);
            }
            if(isset($filter->start_date) || isset($filter->end_date)){
                $from=isset($filter->start_date)?date($filter->start_date):date('1990-01-01');
                $to=isset($filter->end_date)?date($filter->end_date):date('2099-01-01');
                $expenses=$expenses->whereDate('date','<=',$to)->whereDate('date','>=',$from);
            }
            $count=$expenses->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            $expenses = $expenses->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $expenses=$expenses->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        return $this->sendResponse(ExpenseResource::collection($expenses->get()), 'Expenses retrieved successfully.',$count);
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
            'expenses' => 'required|array',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $expenses=$input['expenses'];
        foreach($expenses as $expense){
            $expense['date']=date("Y-m-d",strtotime($expense['date']));
            Expense::create($expense);
        }
        return $this->sendResponse(null, 'Expenses created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expense = Expense::find($id);

        if (is_null($expense)) {
            return $this->sendError('Expense not found.');
        }

        return $this->sendResponse(new ExpenseResource($expense), 'Expense retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expense $expense)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'expense_type_id' => 'required',
            'amount' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $expense->expense_type_id=$input['expense_type_id'];
        if(isset($input['extra']))
            $expense->extra=$input['extra'];
        $expense->amount=$input['amount'];
        $expense->save();
        return $this->sendResponse(($expense), 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return $this->sendResponse([], 'Expense deleted successfully.');
    }
}
