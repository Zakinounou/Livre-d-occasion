<?php

namespace App\Http\Controllers;

use App\Models\auteur;
use App\Models\exemplaire;
use App\Models\photo;
use App\Models\livre;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use function PHPUnit\Framework\returnSelf;

class livreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
{
        
        $ids = explode(',', $request->ids);
        $ids = array_map('intval', $ids); 
        //$ids=[1];
        print"ids";
        $uploadedImages = [];


        $livresproposer = $request->session()->get('livresproposer',[]);
        foreach($livresproposer as $livre ){
            foreach ($livre->file('images') as $image) {
                // Store each image in the specified directory
                $imagePath = $image->store('public/images');
                $uploadedImages[] = $imagePath;
            }
        }
       


        foreach($livresproposer as $livre ){
            if(in_array($livre['idt'],$ids)){
                $livre_exist=Livre::where('titre', $livre['titre'])->first();
                $auteur=auteur::where('nom',$livre['auteur_nom'])->where('prenom',$livre['auteur_prenom'])->first();
                    
                if(($livre_exist)){
                    exemplaire::create([
                        'id'=>$livre_exist->id,
                        'etat'=>$livre['etat'],
                        'prix'=>$livre['prix'],
                    ]);
                    print "exemplaire ajoute";
                }
                else
                {   
                    if(!$auteur){
                    $auteur=auteur::create([
                        "nom"=>$livre['auteur_nom'],
                        "prenom"=>$livre['auteur_prenom'],
                        "Nationalite"=>$livre['Nationalite'],
                    ]);
                    

                }
                    $auteur_con=auteur::where('ida',$auteur->ida)->first();
                    print "\nauteur ajoute";

                     $new_livre=livre::create([
                        "id"=>$livre['id'],
                        "titre"=>$livre['titre'],
                        "ida"=>$auteur_con->ida,
                        "anneePublication"=>$livre['anneePublication'],
                        "category"=>$livre['category'],
                        "description"=>$livre['description'],
                        "nbex"=>0,
                        "etatcom"=>'acheté',
                    ]);
                    print "new livre ajoute"; 
                
                
                //$photos_path = explode(',', $livre['photos']);
               // foreach($photos_path as $path){
                    foreach ($livre->file('photos') as $photo) {
                    $imagePath = $photo->store('public/images');
                    photo::create([
                    "path"=>$imagePath,
                    "isbn"=>$new_livre->id,
                    ]);
                    print "new photo ajoute";
                }
             }   
               
            }else print" n'est pas coucher";
        }
    
        $request->session()->flush();
        print "session deleted ";

        return "Livres ajoutés avec succès ";
    }

    public function uploadImages(Request $request)
{
    // Validate the uploaded files
    $request->validate([
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
    ]);

    
    // Loop through each uploaded image
        foreach ($request->file('photos') as $photo) {
            $imagePath = $photo->store('public/images');
            photo::create([
            "path"=>$imagePath,
            "isbn"=>2038635138496,
            ]);
            print "new photo ajoute";
        
    }

    return 'done';
}


    
    public function store(Request $request)
    {
        
        $validatedData=$request->validate([
            'id'=>'required',
            'auteur_nom'=>'required|string|max:255',
            'auteur_prenom'=>'required|string|max:255',
            'Nationalite'=>'required|string|max:255',
            'titre'=>'required|string|max:255',
            'anneePublication'=>'required|string|max:255',
            'description'=>'required|string|max:255',
            'category'=>'required|string|max:255',
            'etat'=>'required|string|max:255',
            'prix'=>'required|string|max:255',  
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Example validation rules for an image upload
            
        ]);

        $photoPaths = [];

        // Check if photos are present in the request
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                // Store each photo in the 'uploads' directory and get its path
                $path = $photo->store('uploads', 'public');
                $photoPaths[] = $path; // Add the path to the array
            }


       

        $livres = $request->session()->get('livresproposer', []);
        $livreproposer = [   

            'idt'=>count($livres)+1,
            'auteur_nom' => $validatedData['auteur_nom'],
            'auteur_prenom' => $validatedData['auteur_prenom'],
            'Nationalite' => $validatedData['Nationalite'],
            'id'=>$validatedData['id'],
            'titre' => $validatedData['titre'],
            'anneePublication' => $validatedData['anneePublication'],
            'description' => $validatedData['description'],
            'category' => $validatedData['category'],
            'etat' => $validatedData['etat'],
            'prix' => $validatedData['prix'],
            'photos' => $photoPaths,
            
        ];
        

        $livres[] = $livreproposer;
        $request->session()->put('livresproposer',$livres);


        return  "des livres ont été proposés avec succès";
    }}

    public function showproposedlivres(Request $request)
    {
        return $request->session()->all('livresproposer');
    }
    public function Set_etatcom_livre(Request $request){
        $livre=Livre::find($request->id);
        if($livre){
        if($request->type =='archive') $livre->etatcom=$request->type; 
        if($request->type =='achete') $livre->etatcom=$request->type;
        if($request->type =='vonde') $livre->etatcom=$request->type;
        if($request->type =='a vendr') $livre->etatcom=$request->type;
        $livre->save();
        return "faite";}
        else return "livre untrovalble";
        

    }


    /**
     * Display the specified resource.
     */
    public function showall(Request $request)
    {   
        $livre=DB::table(table:'livres')
        ->where('etatcom','=','a vendr')
        ->select('*')
        ->get();  
        return $livre;
    }

//les fonctions de recherche
    public function recherche(Request $request){
        $typee=request()->type;
        if($typee == "titre"){
            $livre=DB::table(table:'livres')->select('*')->where('titre','=',request()->value)->get();  
            $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livre[0]->id)->get();  
            foreach($livre as $livr){
            $livr->photos = $photos;
        }
            return $livre;

            }
        else if($typee == "isbn"){ 
                $livre=livre::find(request()->value);
                $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livre[0]->id)->get();  
                $livre[0]->photos = $photos;
                    return $livre;
                }
        else if($typee == "auteur"){
        if(request()->value && !request()->value2)
        {
            $livre = DB::table('livres as l')
            ->join('auteur as r', 'l.ida', '=', 'r.ida')
            ->select('l.*')
            ->where('r.nom', 'like', '%' . request()->value . '%')
            ->get();
            foreach($livre as $livr){
                $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livr->id)->get();  
                $livr->photos = $photos;
            }            return $livre;
        }elseif(request()->value2 && !request()->value){
            
            $livre = DB::table('livres as l')
            ->join('auteur as r', 'l.ida', '=', 'r.ida')
            ->select('l.*')
            ->where('r.prenom', 'like', '%' . request()->value2 . '%')
            ->get();
            $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livre[0]->id)->get();  
            $livre[0]->photos = $photos;
            return $livre;
        }elseif(request()->value2 && !request()->value){
            $livre = DB::table('livres as l')
            ->join('auteur as r', 'l.ida', '=', 'r.ida')
            ->select('l.*')
            ->where('r.nom', 'like', '%' . request()->value . '%')
            ->where('r.prenom', 'like', '%' . request()->value2 . '%')
            ->get();  
            $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livre[0]->id)->get();  
            $livre[0]->photos = $photos;
            return $livre;
        }
    
    }
            //$livre=DB::table(table:'livres')->select('*')->where('ida','=',request()->value)->get();  
              //  return $livre;
            
        else if($typee == "category"){
            $livre=DB::table(table:'livres')->select('*')->where('category','=',request()->value)->get();  
            $photos=DB::table(table:'photo')->select('path')->where('isbn','=',$livre[0]->id)->get();  
            $livre[0]->photos = $photos;    
            return $livre;
            }
        return 'not found';
    
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {   
    }

    /** 
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    { 
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
    $livre=Livre::find($request->id);
    if($livre) $livre->etatcom='archivé';
    else return response()->json(['error'=> 'livre introuvable']);
    return response()->json(['error'=>'Le livre a été supprimé avec succès']);
    }

    public function change(Request $request)
    { 
        $livre = Livre::find($request->id);

        if (!$livre) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        // Validate the request data (you can adjust this based on your validation rules)
            $validatedData=$request->validate([
                'titre'=>'string|max:255',
                'auteur'=>'string|max:255',
                'anneePublication'=>'string|max:255',
                'etat'=>'string|max:255',
                'prix'=>'string|max:255',
                'photo1' => 'string', // Example validation rules for an image upload
                'photo2' => 'string', // Example validation rules for the photo upload

                
        ]);
            // Add more validation rules for other fields as needed
    
        // Update only the fields that are not empty in the request
        foreach ($validatedData as $key => $value) {
            if (!empty($value)) {
                $livre->{$key} = $value;
            }
        }

        // Save the changes if any attributes have changed
        if ($livre->isDirty()) {
            $livre->save();
            return response()->json(['message' => 'Book updated successfully']);
        } else {
            return response()->json(['message' => 'No changes detected'], 200);
        }
    }
    public function test($request){
        $tet=$request->par;
        print $tet;
        
    }
    }
    
