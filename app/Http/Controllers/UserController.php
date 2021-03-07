<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
class UserController extends Controller
{
	public function authenticate(Request $request)
	{
		$credentials = $request->only('email', 'password');

		try {
			if (! $token = JWTAuth::attempt($credentials)) {
				return response()->json(['error' => 'invalid_credentials'], 400);
			}
		} catch (JWTException $e) {
			return response()->json(['error' => 'could_not_create_token'], 500);
		}

		return response()->json(compact('token'));
	}

	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'password' => 'required|string|min:6|confirmed',
		]);

		if($validator->fails()){
			return response()->json($validator->errors()->toJson(), 400);
		}

		$user = User::create([
			'name' => $request->get('name'),
			'email' => $request->get('email'),
			'password' => Hash::make($request->get('password')),
		]);

		return response()->json(compact('user'),201);
	}


	public function logout(Request $request)
	{		
		$token = $request->header( 'Authorization' );

		try {
			JWTAuth::parseToken()->invalidate( $token );

			return response()->json([
				'success' => true,
				'message' => 'User logged out successfully'
			],200);
		} catch (JWTException $exception) {
			return response()->json([
				'success' => false,
				'message' => 'Sorry, the user cannot be logged out'
			], 500);
		}
	}


}
