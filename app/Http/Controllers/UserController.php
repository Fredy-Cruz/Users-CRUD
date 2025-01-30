<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    //Method to save the user
    public function saveUser(User $user){
        try{
            $user->save();
        }catch(Exception $e){
            return response()->json([
                "message" => "Error has ocurred"
            ], 500);
        }
    }

    //Method to validate searching the user by id
    public function findUser(User $user,string $id){
        try{
            //Apply CRUD operations jut fot available users
            $user = User::find($id)->where('disabled', false)->where('role', 'user');
        }catch(Exception $e){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }    
    }

    //Display a listing of the resource.
    public function index()
    {
        $users = User::where('disabled', false)->where('role', 'user')->get();

        //In case there are no users registered
        if($users->isEmpty()){
            return response()->json([
                'message' => 'No users found'
            ], 404);
        }

        return response()->json([
            'message' => 'Get all users',
            'data' => $users
        ], 200);
    }


    //Store a newly created resource in storage.
    public function store(Request $request)
    {
        //Validating the request
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        //Check if the email already exists
        $user = User::where("email", $request->email)->first();
        if($user){
            return response()->json([
                "message" => "Email already exists"
            ], 400);
        }

        //Store the data
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $this->saveUser($user);

        return response()->json([
            'message' => 'Store data',
            'data' => $user
        ], 201);
    }

    //Display the specified resource.
    public function show(string $id)
    {
        $user = User::find($id)->where('disabled', false)->where('role', 'user');
        $this->findUser($user);

        return response()->json([
            'message' => 'Get user by id',
            'data' => $user
        ], 200);
    }

    //Update the specified resource in storage.
    public function update(Request $request, string $id)
    {
        //Only allow to update the name
        $request->validate([
            "name" => "required"
        ]);

        $user = User::find($id)->where('disabled', false)->where('role', 'user');
        $this->findUser($user);    

        $user->name = $request->name;

        $this->saveUser($user);
        
        return response()->json([
            "message" => "Data Updated",
            "data" => $user
        ], 200);
    }
    
    //Disable the specified resource from storage.
    public function destroy(string $id)
    {
        $user = User::find($id)->where('disabled', false)->where('role', 'user');
        $this->findUser($user);

        $user->disabled = true;
        
        $this->saveUser($user);
        
        return response()->json([
            'message' => 'Disabled the user',
            'data' => $user
        ], 200);        
    } 
}
