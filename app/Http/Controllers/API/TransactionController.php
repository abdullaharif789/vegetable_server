<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Transaction;
use App\Models\PurchaseInvoice;
use Validator;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\ETransaction as ETransactionResource;
use DB;

class TransactionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $party_id=$request->get("party_transactions_id");
        if($request->get("totalUnpaid")){
            $data=DB::select("SELECT sum(amount) as total FROM transactions WHERE paid=0");
            return $data[0]->total;
        }
        if(isset($party_id)){
            $transactions = Transaction::with('party')->where('party_id',$party_id);
        }else{
            $transactions = Transaction::with("party");
        }
        $count=$transactions->get()->count();
         if($request->get("filter")){
            $filter=json_decode($request->get("filter"));
            if(isset($filter->paid))
                $transactions=$transactions->where('paid',strtolower($filter->paid)=="paid"?1:0);
            if(isset($filter->date))
                $transactions=$transactions->where('date',"like","%".$filter->date."%");
            if(isset($filter->party_id))
                $transactions=$transactions->where('party_id',$filter->party_id);
            if(isset($filter->amount) && isset($filter->weeks)){
                $transactions=$transactions->where('paid',0)->where(
                     function($query) use($filter) {
                         return $query
                                ->where('amount',">",$filter->amount)
                                ->orWhere("created_at","<",date('Y-m-d',strtotime($filter->weeks)));
                        });
            }else{
                if(isset($filter->amount))
                    $transactions=$transactions->where('paid',0)->where('amount','>',$filter->amount);
                if(isset($filter->weeks))
                    $transactions=$transactions->where('paid',0)->where("created_at","<",date('Y-m-d',strtotime($filter->weeks)));
            }
            $count=$transactions->get()->count();
        }
        if($request->get("sort")){
            $sort=json_decode($request->get("sort"));
            if(isset($sort[0]) && isset($sort[1]))
                $transactions = $transactions->orderBy($sort[0],$sort[1]);
        }
        if($request->get("range")){
            $range=json_decode($request->get("range"));
            $transactions=$transactions->offset($range[0])->limit($range[1]-$range[0]+1);
        }
        if(!isset($party_id)){
            $transactions=$transactions->groupBy('party_id')->selectRaw('party_id,SUM(amount) as amount,date,created_at');
            return $this->sendResponse(TransactionResource::collection($transactions->get()), 'Transactions retrieved successfully.',$count);
        }
        return $this->sendResponse(ETransactionResource::collection($transactions->get()), 'Transactions retrieved successfully.');
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
            'amount' => 'required',
            'date'=> 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input['paid']=isset($input['paid']) && $input['paid'] == "paid"?1:0;
        $transaction = Transaction::create($input);
        $transaction['date']=date('Y-m-d h:i:s', strtotime($transaction['date']));
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Transaction::find($id);
  
        if (is_null($transaction)) {
            return $this->sendError('Transaction not found.');
        }
   
        return $this->sendResponse(new ETransactionResource($transaction), 'Transaction retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'paid'=> 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $transaction->paid=isset($input['paid']) && $input['paid'] == "Paid"?1:0;
        $transaction->save();
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
   
        return $this->sendResponse([], 'Transaction deleted successfully.');
    }
}
