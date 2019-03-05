<?php

namespace App\Http\Controllers\SendMail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SendMailController extends Controller{
	public function send(Request $request){
		if(empty($request->name)){
			return response()->json(['message'=>'Name is empty'], 422);
		}
		if(empty($request->email)){
			return response()->json(['message'=>'E-mail is empty'], 422);
		}
		if(!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
			return response()->json(['message'=>'E-mail is invalid'], 422);
		}
		if(empty($request->message)){
			return response()->json(['message'=>'Message is empty'], 422);
		}

		$sendgrid_key = env('SENDGRID_KEY');

		if(!$sendgrid_key){
			\Log::error('[SendMailController] The Sendgrid key is not seted at .env file.');

			return response()->json(['message'=>'An error occurred! Please contact the system administrator.'], 500);
		}

		$sendgrid = new \SendGrid($sendgrid_key);
		$email    = new \SendGrid\Mail\Mail(); 


		$email_message = "<table><tr><td>Name:<td><td>$request->name</td></tr><tr><td>Email:<td><td>$request->email</td></tr><tr><td>Message:<td></tr></table><pre>$request->message</pre>";

		\Log::info('[SendMailController]'.env('CONTACT_EMAIL'));
		$email->addTo(env('CONTACT_EMAIL'));
        $email->setFrom('no-reply@donare.com');
        $email->setSubject("Contact from $request->name");
        $email->addContent("text/html", $email_message);

		try {
		    $sendgrid_response = $sendgrid->send($email);
		    
		    \Log::info('[SendMailController] A message has been sent and the sendgrid status code is '.$sendgrid_response->statusCode());
		    \Log::info("[SendMailController] The content of this message is $email_message");
		    
		} catch (Exception $e) {
			\Log::error('[SendMailController] Exception on send email!');
			\Log::error('[SendMailController] '.$e);

			return response()->json(['message'=>'An error occurred! Please contact the system administrator.'], 500);
		}

		return response()->json(['message'=>'Message sent successfuly!'], 200);
	}
}