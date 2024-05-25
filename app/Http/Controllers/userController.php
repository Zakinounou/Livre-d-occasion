<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Usersarchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Monolog\Handler\IFTTTHandler;

class userController extends Controller
{
    function registere(Request $request){
        if(!(Usersarchive::where('email', $request->email)->first())){
        $user= User::create([
            "nom"=>request()->nom,
            "prenom"=>request()->prenom,
            "adress"=>request()->adress,
            "numeroDetel"=>request()->numeroDetel,
            "email"=>request()->email,
            "password"=>request()->password
        ]);
        return  $user;
    }
    return "Il vous est interdit de vous inscrire pour violer la confidentialité de l'application";
    }

    function logine(Request $request){
        $user=User::where('email',$request->input('email'))->first();
        if(!$user){
            return 'you need to register';
        }
        if(!Hash::check($request->input('password'),$user->password)){
            return 'wrong password';
        }

        $token=$user->createToken('auth_token');    
        return response()->json(["token"=>$token->plainTextToken]);
    }
    function archiverClient(Request $request){
        $email=$request->email;
        $user = User::where('email', $email)->first();
        if($user){
        Usersarchive::create([
            "nom"=>$user->nom,
            "prenom"=>$user->prenom,
            "adress"=>$user->adress,
            "numeroDetel"=>$user->numeroDetel,
            "email"=>$user->email,
            "password"=>$user->password
        ]);
        $user->delete();
        return
         response()->json(['message' => 'User archived and deleted successfully']);
        }
        else {
            return response()->json(['error' => 'User not found'], 404);
        }
        
    }
   
}