<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    //Method to save the user
    public function saveUser(User $user){
        try{
            $user->save();
            return $user;
        }catch(Exception $e){
            return response()->json([
                "message" => "Could not save the user"
            ], 500);
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
        $validator = Validator::make($request->all(),[
            'name' => 'required | min:2 | max:90',
            'email' => 'required | email | unique:users,email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => "Invalid request"
            ], 400);
        }

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
            'message' => 'Data stored',
            'data' => $user
        ], 201);
    }

    //Display the specified resource.
    public function show(string $id)
    {
        try{
            //Apply CRUD operations just for available users
            $user = User::where('disabled', false)->where('role', 'user')->where('id', $id)->first();
            if (!$user) {
                throw new Exception("User not found");
            }
        }catch(Exception $e){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }    

        return response()->json([
            'message' => 'Get user by id',
            'data' => $user
        ], 200);
    }

    //Update the specified resource in storage.
    public function update(Request $request)
    {
        //Only can update self name
        $ActualUser = auth()->user();
        $id = $ActualUser->id;

        //Validating the request
        $validator = Validator::make($request->all(),[
            'name' => 'required | min:2 | max:90'
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => "Invalid Request"
            ], 400);
        }
        
        try{
            $user = User::where("id", $id)->first();
            if (!$user) {
                throw new Exception("User not found");
            }

            //Only allow to update the name
            $user->name = $request->name;
            $this->saveUser($user);
        }catch(Exception $e){
            return response()->json([
                "message" => "An error has ocurred"
            ], 400);
        }      

        return response()->json([
            "message" => "Data Updated",
            "data" => $user
        ], 200);
    }
    
    //Disable the specified resource from storage.
    public function destroy(string $id)
    {
        try{
            //Apply CRUD operations just for available users
            $user = User::where('disabled', false)->where('role', 'user')->where('id', $id)->first();
            if (!$user) {
                throw new Exception("User not found");
            }

            $user->disabled = true;
            $this->saveUser($user);
        }catch(Exception $e){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }      

        return response()->json([
            'message' => 'Disabled the user',
            'data' => $user
        ], 200);        
    } 

    //Login
    public function login(Request $request){

        //Validating the request
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => "Invalid request"
            ], 400);
        }
        
        /* $credentials = $request->only("email", "password"); */
        $credentials = $request;

        //Verifying email
        $user = User::where("email", $credentials["email"])->first();
        if(!$user){
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }

        //Verifying password
        if(!password_verify($credentials["password"], $user->password)){
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }

        //Generate token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            "message" => "Login succes",
            "data" => $token
        ]);    
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
    
            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not log out, please try again'
            ], 500);
        }
    }


    //Refresh Token
    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return response()->json([
                'token' => $newToken
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Could not refresh token'
            ], 500);
        }
    }

}