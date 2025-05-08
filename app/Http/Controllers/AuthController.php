<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function index()
    {
        $user = User::all();

        return $this->Ok($user);
    }

    public function show(string $id)
    {
        $user = User::find($id);
        
        if(empty($user)){
            return $this->NotFound("User not found!");
        }
        return $this->Ok($user);
    }

    public function register(Request $request){
        $validator = validator()->make($request->all(), [
            "username" => "required|alpha_dash|min:4|max:32|unique:users",
            "email"=> "required|unique:users|email|max:255",
            "password" => "required|min:8|max:64|confirmed",
            "first_name" => "required|string|max:255",
            "last_name" => "required|string|max:255",
            "phone_number" => "required|string|max:255",
            "address" => "required|string|max:500"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $user = User::create($validator->validated());
        $user->profile()->create($validator->validated());
        $user->profile;

        return $this->Created($user, "Registered!");
    }

    public function checkToken(Request $request){
        $user = $request->user();
        $user->profile;
        return $this->Ok($user);
    }
    public function logout(Request $request){
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return $this->Ok(null, "Logout!");
    }   

    public function update(Request $request, string $id)
    {

        $user = User::find($id);
        
        if(empty($user)){
            return $this->NotFound("User not found!");
        }

        $validator = validator()->make($request->all(), [
            "username" => "sometimes|alpha_dash|min:4|max:32|unique:users,username,$id",
            "email"=> "sometimes|unique:users,email,$id|email|max:255",
            "password" => "sometimes|min:8|max:64|confirmed",
            "first_name" => "sometimes|string|max:255",
            "last_name" => "sometimes|string|max:255",
            "phone_number" => "sometimes|string|max:255",
            "address" => "sometimes|string|max:255"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $user->update($validator->validated());

        if ($request->hasAny(['first_name', 'last_name', 'phone_number', 'address'])) {
            $user->profile()->update($request->only(['first_name', 'last_name', 'phone_number', 'address']));
        }
        
        return $this->Ok($user->load('profile'), "$user->username's information has been updated!");

    }

    public function destroy(string $id)
    {
        $user = User::find($id);
        
        if(empty($user)){
            return $this->NotFound("User not found!");
        }


        $user->delete();

        return $this->Ok($user,"User has been deleted!");
    }

    public function login(Request $request){
        $validator = validator()->make($request->all(), [
            "username" => "required",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);   
        }

        $inputs = $validator->validated();

        if(!auth()->attempt($inputs)){
            return $this->Unauthorized();
        }

        $user = auth()->user();
        $token = $user->createToken("api")->plainTextToken;

        $user->profile;

        return $this->Ok($user, "Logged in success!", ['user_id' => $user->id,"token" => $token]);
    }
}
