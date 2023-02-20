<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(LoginRequest $request){
        try {
            $credentials = $request->validated();
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Invalid Credentials'
                ],'Authentication Failed', 403, 'forbidden');
            } 
            
            $user = User::findByEmail($credentials['email']);
            $token = $user->createToken('authToken')->plainTextToken;

            return  ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong', 
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function register(RegisterRequest $request){
        try {
            $user = User::create($request->validated());
            $token = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Registration Success', 201, 'created');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Registration Failure', 500);
        }
    }

    public function logout(Request $request){
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
    
    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(), 'Data profile user berhasil diambil');
    }

    public function updateProfile(Request $request){
        $user = Auth::user();
        $user->update($request->all());

        return ResponseFormatter::success($user, 'Profile Updated');
    }

    public function updatePhoto(Request $request){
        $request->validate(
            ['file' => 'required|image|max:2045']
        );
        
        if($request->file('file')){
            $file = $request->file->store('assets/user', 'public');

            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success($user, 'Profile Photo Successfully Updated');
        }
    }


}
