<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Usersarchive;

class adminController extends Controller
{
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
