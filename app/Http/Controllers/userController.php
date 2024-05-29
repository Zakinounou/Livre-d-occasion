<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\livre;

use App\Models\Usersarchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
    public function best_vente(){
        $livre=1;
        // Step 1: Fetch the sales count for each idEx
        
        $livrevende = DB::table('exemplaire')
            ->select('exemplaire.isbn', DB::raw('COUNT(*) as sales_count'))
            ->where('etat_Com','=','vende')
            ->groupBy('exemplaire.isbn')
            ->orderByDesc('sales_count')
            ->limit(5)->
            get();
        
        $isbns = $livrevende->pluck('isbn');    
        $livre= DB::table('livres')
        ->select('*')
        ->whereIn('id', $isbns)
        ->get();
        return $livre;
    }    

public function derniersLivresAjoutes()
{
    $derniersLivres = Livre::orderBy('id', 'desc')->take(5)->get();

    return $derniersLivres;
}

public function photo_Profile(Request $request)
{
    // Validate the uploaded files
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
    ]);
    
    $imagePath = $request->image->store('public/profil');
    $userid=auth()->id();
    $user = user::where('id', $userid)->first();
    if (!$user) {
        return response()->json(['error' => 'you have to register first'], 404);
    }
    $new='';
    if($user->photo){
       Storage::delete($user->photo);
        $new='new ';
    }
    $user->photo = $imagePath;
    $user->save();    
    return $new."photo ajoute avec seccu";
}

public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        return response()->json(
            [
                'message' => 'Logged out'
            ]
        );

    }



    public function updateaddress(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'address' => 'required|string|max:255',
    
        ]);
     
        $userid=auth()->id();
        $user = user::where('id', $userid)->first();
        if($user){
            $user->update([
                'adress' => $validatedData['address']]);
                return response()->json(['mssage' => 'address updated successfully']);
        }
        
            return response()->json(['error' => 'user not found'], 404);
        }

    public function updatemobile(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'mobile' => [
                'required',
                'regex:/^(06|07|05)\d{8}$/',
            ],
        ], [
            'mobile.regex' => 'The mobile number must start with 06, 07, or 05 and be exactly 10 digits long.',
        ]);
        $userid=auth()->id();
        $user = user::where('id', $userid)->first();
        if($user){
            $user->update([
                    'numeroDetel' => $validatedData['mobile']]);
                    return response()->json(['mssage' => 'numero de telephone updated successfully']);
            }
            
                return response()->json(['error' => 'user not found'], 404);
    }


}
