<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use DB, Hash, Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;

class AuthController extends Controller
{

	/*
	 * API Register
	 * @param Request $request
	 * @param \Illuminate\Http\JsonResponse
	 */	
	public function register( Request $request )
	{
		$rules = [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
		];

		$input = $request->only(
			'name',
			'email',
			'password',
			'password_confirmation',
		);

		$validator = Validator::make( $input, $rules );

		if( $validator->fails() ) :
			$error = $validator->messages()->toJson();
			return  response()->json(['success'=>false, 'error'=>$error]);
		endif;

		$name = $request->name;
		$email = $request->email;
		$password = $request->password;

		$user = User::create(['name'=> $name, 'email' => $email, 'password' => 	Hash::make($password)]);

		$verification_code = str_random( 30 );

		DB::table('user_verifications')->insert(['user_id'=>$user_id, 'token'=>$verification_code]);

		$subject = 'Please verify your email address';
		Mail::send('email.verify', ['name'=>$name, 'verification_code' => $verification_code],
			function( $mail ) use ( $email, $name, $subject ){
				$mail->from(getenv('FROM_EMAIL_ADDRESS'), "From User / Company Name Goes Here");
				$mail->to( $email, $name );
				$mail-.subject($subject);
			}
		);
		return response()->json(['success'=>true, 'message'=> 'Thanks for signing up! Please check your email to complete registration.']);
	}


}
