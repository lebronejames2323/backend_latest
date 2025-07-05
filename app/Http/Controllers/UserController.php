<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        return $this->Ok($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            "name"=> "required|unique:users|min:4|alpha_dash|max:32",
            "email"=> "required|unique:users|email|max:255",
            "password"=> "required|min:8|max:255",
        ]);
        
        if ($validator->fails()){
            return $this->BadRequest($validator);
        }

        $user = User::create($validator->validated());

        return $this->Created($user, "User has been created!");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        
        if(empty($user)){
            return $this->NotFound("User not found!");
        }
    }
}
