<?php


namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
class BaseController extends Controller
{
    function __construct() {
        // date_default_timezone_set('Europe/London');
    }
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message,$total=null)
    {
        $response = $result;
        return response()->json($response, 200,['Content-Language'=>$total]);
    }
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 422)
    {
        if(gettype($errorMessages)=="object")
            return response()->json(implode(' ', $errorMessages->all()), $code);
        else
            return response()->json($error, $code);
    }
    protected function setDate($date){
        return Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->setTimezone('Europe/London');
    }
    protected function arraySearch($toBeSearch,$array){
        foreach($array as $item){
            if($item->id==$toBeSearch) return true;
        }
        return false;
    }
}
