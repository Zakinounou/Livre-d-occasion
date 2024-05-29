<?php

namespace App\Http\Controllers;

use App\Models\commande;
use App\Models\exemplaire;
use App\Models\User;
use App\Models\livre;
use App\Models\Panier;

use Illuminate\Support\Facades\Validator;

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

public function derniersLivresAjoutes(){
    $derniersLivres = Livre::orderBy('id', 'desc')->take(5)->get();

    return $derniersLivres;
}
public function photo_Profile(Request $request){
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

public function logout(Request $request){

        $request->user()->tokens()->delete();

        return response()->json(
            [
                'message' => 'Logged out'
            ]
        );

    }



    
public function updateaddress(Request $request){
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
public function updatemobile(Request $request){
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


public function store(Request $request){
        // Validate the incoming request without flashing session data
        $validator = Validator::make($request->all(), [
            'avec_livr' => 'required|string|max:5',
            
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        // Get the existing proposed books from the session
        $commandes = $request->session()->get('commandesproposer', []);
        $userId=auth()->id();
        $panier = Panier::where('idC', $userId)->first();
        // Add the new book data along with photo pat

    $commandeproposer = [
    'avec_livr' => $validatedData['avec_livr'],
    'idAch' => $userId,
    'idPa' => $panier->idPanier,
    ];

        // Add the new proposed book to the session
        $commandes[] = $commandeproposer;
        $request->session()->put('commandesproposer', $commandes);

        // Return a JSON response indicating success
        return response()->json(['message' => 'commande proposed successfully!', 'data' => $commandeproposer], 200);
    }


public function etablireCommandes(Request $request){
        // Get the IDs from the request and convert them to integers
        $ids = explode(',', $request->ids);
        $ids = array_map('intval', $ids); 
    
        // Get the proposed books from the session
        $commandesproposer = $request->session()->get('commandesproposer',[]);
    if($ids){
        // Loop through each proposed book
        foreach ($commandesproposer as $commande) {
            // Check if the book ID is in the list of IDs from the request
            if (in_array($commande['idt'], $ids)) {
                // Check if the book already exists in the database
               
    
                    // Create a new book if it does not exist
                    $new_commande = commande::create([
                        "avec_livr" => $commande['avec_livr'],
                        "idAch" => $commande['idAch'],
                        "idpan" => $commande['idpan'],
                    ]);
                    print "Nouveau commande etablire\n";
    
                    // Create a relationship between the author and the book
                    
                    // Loop through each photo path and create a photo record
                   
                }
                else {
                print "\nCette commande n'est pas sélectionné\n";
            }
        }
    
        // Flush the session after processing
        $request->session()->flush();
        print "Session supprimée\n";
    
        return "commandes etablire avec succès";
    }
    else{
        return "aucun commande a ete etablire";
    }
    }


    public function showRequestCommande(Request $request)
    {
        // Retrieve 'commandesproposer' from the session

        $requestedCommandes = $request->session()->get('commandesproposer', []);

    
        // Initialize an array to hold all 'avec_livr' values
        $avecLivrValues = [];
    
        // Check if 'commandesproposer' exists and is an array
        if (is_array($requestedCommandes)) {
            // Loop through each item in the 'commandesproposer' array
            foreach ($requestedCommandes as $commande) {
                // Add 'avec_livr' value to the array
                $avecLivrValues[] = $commande['avec_livr'];
    
                // Find the user by 'idAch'
                $user = User::find($commande['idAch']);
                $Rcommande[]=$user;
    
                // Get panier records where 'idAch' matches
                $panier = Panier::where('idC', $commande['idAch'])->get();
    
                // Assume you want to get the first Panier item for simplicity
                if ($panier->isNotEmpty()) {
                    $firstPanierItem = $panier->first();
    
                    // Find the exemplaire by 'idEx'
                    $exe = Exemplaire::where('idex', $firstPanierItem->idEx)->first();
    
                    if ($exe) {
                        // Find the livre by 'isbn'
                
                        $livre = Livre::find($exe->isbn);

                        $Rcommande[]=$livre;

    
                        
                    }
                }
                $full[]=$Rcommande;
            }

        }
    return $full;
        // Return a JSON response with all 'avec_livr' values (if needed)
        return response()->json(['avec_livr' => $avecLivrValues]);
    }
    








    }    
        






