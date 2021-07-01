<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Party;
use Illuminate\Support\Facades\Auth;
use Validator;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    private $adminUsername="admin";
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required',
            // 'avatar' =>  'required',
            // 'business_name' => 'required|unique:parties',
            // 'address' => 'required',
            // 'contact_number' => 'required|unique:parties',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['name']=strtolower($input['name']);
        $input['username']=strtolower($input['username']);
        $user = User::create($input);
        /*Create Party Object*/
        $input['user_id']=$user->id;
        /*Extra Flieds*/
        // $input['avatar']=$input['avatar'];
        // $input['business_name']=$input['business_name'];
        // $input['address']=$input['address'];
        // $input['contact_number']=$input['contact_number'];
        /*Create Party*/
        $party=Party::create($input);
        $success['token'] =  $user->createToken('Tutoras')->accessToken;
        $success['name'] =  ucwords($user->name);
        $success['username'] =  $user->username;
        $success['email'] =  $user->email;
        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if($request->username==$this->adminUsername)
        {
           return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],401);
        }
        else{
            if(Auth::attempt(['username' => $request->username,'password' => $request->password])){
                $user = Auth::user();
                $success['token'] =  $user->createToken('Tutoras')->accessToken;
                $success['name'] =  ucwords($user->name);
                $success['email'] =  $user->email;
                $success['username'] =  $user->username;
                return $this->sendResponse($success, 'User login successfully.');
            }
            else{
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }
        }
    }
    public function loginadmin(Request $request)
    {
        if($request->username==$this->adminUsername)
        {
            if(Auth::attempt(['username' => $request->username,'password' => $request->password])){
                $user = Auth::user();
                $success['token'] =  $user->createToken('Tutoras')->accessToken;
                $success['name'] =  ucwords($user->name);
                $success['username'] =  $user->username;
                $success['email'] =  $user->email;
                return $this->sendResponse($success, 'Admin login successfully.');
            }
            else{
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],401);
            }
        }
        else{
             return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],401);
        }
    }
    /**
     * Logout
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        if (Auth::check()) {
           Auth::user()->AauthAcessToken()->delete();
           return $this->sendResponse('Success', 'User logout successfully.');
        }
        return $this->sendResponse('Unauthorised.', 'User logout error.');
    }
}

