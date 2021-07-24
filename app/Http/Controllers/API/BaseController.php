<?php


namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        // $response = [
        //        'success' => true,
        //        'data'    => $result,
        //        'message' => $message,
        //    ];

        $response = $result;
        
        return response()->json($response, 200);
    }
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 422)
    {
    	//$response = [
        //  'success' => false,
        //   'message' => $error,
        //];
        if(gettype($errorMessages)=="object")
            return response()->json(implode(' ', $errorMessages->all()), $code);
        else
            return response()->json($error, $code);
    }
    protected function arraySearch($toBeSearch,$array){
        foreach($array as $item){
            if($item->id==$toBeSearch) return true;
        }
        return false;
    }
}
