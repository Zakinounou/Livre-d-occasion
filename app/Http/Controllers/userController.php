<?php

namespace App\Http\Controllers;

use App\Models\commande;
use App\Models\exemplaire;
use App\Models\User;
use App\Models\livre;
use App\Models\localisation;
use App\Models\Panier;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Livraison;
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
  //     $request->session()->flush();
//return 1;
        $validator = Validator::make($request->all(), [
            'avec_livr' => 'required|string|max:5',
            'dist'=>'required |string',
            
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
if(!$panier){
    return response()->json(['errors' => 'le panier est vide !'], 423);}
    $commandeproposer = [
    'idt' => count($commandes) + 1,
    'avec_livr' => $validatedData['avec_livr'],
    'idAch' => $userId,
    'dist'=>$validatedData['dist'],
    'idPa' => $panier->id,

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
    
        // Get the proposed commandes from the session
        $commandesproposer = $request->session()->get('commandesproposer', []);
    
        if (!empty($ids)) {
            // Track if any commandes were processed
            $processed = false;

            // Loop through each prorposed commande
            foreach ($commandesproposer as $commande) {

                // Check if the 'idcommande' key exists in the current commande
                if (isset($commande['idt'])) {
                    // Check if the commande ID is in the list of IDs from the request
                    if (in_array($commande['idt'], $ids)) {
                        // Create a new commande
                        $localisation = Localisation::where('idAch', $commande['idAch'])
                        ->where('valide', 'oui')
                        ->first();
                        if($localisation){
                        $localisation->valide='no';
                        $localisation->save();


                        commande::create([
                            "avec_livr" => $commande['avec_livr'],
                            "idAch" => $commande['idAch'],
                            "dist"=> $commande['dist'],
                            "idpan" => $commande['idPa'],
                            "idloc" => $localisation->id,
                        ]);
                    }else {
                        return 'svp, fourner un localisation!';
                    }


                        echo "Nouveau commande établie\n";
                        $processed = true;
                        $panierItems = Panier::where('idC', $commande['idAch'])->get();
                        foreach ($panierItems as $item) {
                            Panier::where('id', $item['id'])->delete();
                        }   
                        echo "Panier vidé pour l'utilisateur avec ID " . $commande['idAch'] . "\n";

                    } else {
                        echo "\nCette commande n'est pas sélectionnée\n";
                    }
                } else {
                    echo "\nCommande sans 'idcommande'\n";
                }
            }
    
            // Flush the session after processing
            $request->session()->flush();
            echo "Session supprimée\n";
    
            if ($processed) {
                return response()->json(['message' => 'Commandes établies avec succès'], 200);
            } else {
                return response()->json(['message' => 'Aucune commande n\'a été établie'], 200);
            }
        } else {
            return response()->json(['message' => 'Aucun commande à établir'], 400);
        }
    }
    

    public function showRequestCommande(Request $request)
    {
        // Retrieve 'commandesproposer' from the session
        $requestedCommandes = $request->session()->get('commandesproposer', []);
    
        // Initialize an array to hold the full command information
        $fullCommandes = [];
    
        // Initialize a counter for the commande ID
        $commandeId = 1;
    
        // Check if 'commandesproposer' exists and is an array
        if (is_array($requestedCommandes)) {
            // Loop through each item in the 'commandesproposer' array
            foreach ($requestedCommandes as $commande) {
                // Initialize an array to hold the result for this specific commande
                $Rcommande = ['idcommande' => $commande['idt'],'destination'=> $commande['dist']];
    
                // Find the user by 'idAch'
                $user = User::find($commande['idAch']);
    
                if ($user) {
                    $Rcommande['user'] = [
                        'nom' => $user->nom,
                        'prenom' => $user->prenom
                    ];
                }
    
                // Get panier records where 'idC' matches
                $panier = Panier::where('idC', $commande['idAch'])->get();
    
                // Initialize an array to hold the exemplaire data
                $exemplaireData = [];
    
                // Loop through each panier item
                foreach ($panier as $panierItem) {
                    // Find the exemplaire by 'idEx'
                    $exemplaire = Exemplaire::where('id', $panierItem->idEx)->first();
    
                    if ($exemplaire) {
                        // Find the livre by 'isbn'
                        $livre = Livre::find($exemplaire->isbn);
    
                        if ($livre) {
                            $exemplaireData[] = [
                                'titre' => $livre->titre,
                                'idex' => $exemplaire->id,
                                'prix' => $exemplaire->prix,
                                'etat' => $exemplaire->etat
                            ];
                        }
                    }
                }
    
                // Add the exemplaire data to the current commande result
                $Rcommande['exemplaires'] = $exemplaireData;
    
                // Add the result for this specific commande to the full result set
                $fullCommandes[] = $Rcommande;
    
                // Increment the commande ID for the next iteration
                $commandeId++;
            }
    
            // Return a JSON response with all command information
            return response()->json($fullCommandes);
        }
    
        // Return a default response if no commandes are found
        return response()->json(['message' => 'No commandes found in session']);
    }


    
    public function getAvailableHours($date)
    {
        // Define business hours
        $businessStartHour = 8; // 8 AM
        $businessEndHour = 16; // 4 PM
    
        // Convert date to Carbon instance and check the day of the week
        $date = Carbon::parse($date);
        $dayOfWeek = $date->dayOfWeek;
    
        // Check if the date is a Friday (Carbon::FRIDAY == 5)
        if ($dayOfWeek == Carbon::FRIDAY) {
            return response()->json([
                'date' => $date->toDateString(),
                'available_hours' => [],
                'message' => 'No deliveries are available on Fridays.'
            ]);
        }
    
        // Fetch existing deliveries for the given date
        $existingDeliveries = Livraison::whereDate('datelivr', $date->toDateString())
                                        ->pluck('heure');
    
        // Create a list of all possible hours within business hours
        $availableHours = collect();
        for ($hour = $businessStartHour; $hour < $businessEndHour; $hour++) {
            $availableHours->push($hour . ':00:00');
        }
    
        // Remove the hours that are already booked
        $availableHours = $availableHours->diff($existingDeliveries);
    
        return response()->json([
            'date' => $date->toDateString(),
            'available_hours' => $availableHours->values()
        ]);
    }
    


}   