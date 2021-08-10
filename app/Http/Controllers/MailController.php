<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Mail;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailController extends Controller {
    private $from="info@everydayfreshfood.com";
    private $company="Everyday Fresh Food";
    private $to;
    private $name;
    public function send_email($name,$to,$password,$username) {
        $this->to=$to;
        $this->name=$name;
        $data = array('name'=>$this->name,'email'=>$to,'password'=>$password,'username'=>$username);
        $mail=Mail::send('mail', $data, function($message) {
            $message->to($this->to, $this->name)->subject('Congratulations! for joining '.$this->company.' 👏');
            $message->from($this->from,$this->company);
        });
    }
    public function test(){
        $this->send_email("Abdullah Arif","abdullaharif789@gmail.com","root");
    }
}