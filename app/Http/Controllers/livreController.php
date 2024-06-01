<?php

namespace App\Http\Controllers;

use App\Models\Auteur;
use App\Models\Ecrir;
use App\Models\Exemplaire;
use App\Models\Photo;
use App\Models\Livre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LivreController extends Controller
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
        // Get the IDs from the request and convert them to integers
        $ids = explode(',', $request->ids);
        $ids = array_map('intval', $ids); 

        // Get the proposed books from the session
        $livresproposer = $request->session()->get('livresproposer', []);

        // Loop through each proposed book
        foreach ($livresproposer as $livre) {
            // Check if the book ID is in the list of IDs from the request
            if (in_array($livre['idt'], $ids)) {
                // Check if the book already exists in the database
                $livre_exist = Livre::where('titre', $livre['titre'])->first();
                $auteur = Auteur::where('nom', $livre['auteur_nom'])->where('prenom', $livre['auteur_prenom'])->first();

                if ($livre_exist) {
                    // Create an exemplaire if the book exists
                    Exemplaire::create([
                        'isbn' => $livre_exist->id,
                        'etat' => $livre['etat'],
                        'prix' => $livre['prix'],
                    ]);
                    print "Exemplaire ajouté\n";
                }

                if (!$auteur) {
                    // Create an author if they do not exist
                    $auteur = Auteur::create([
                        "nom" => $livre['auteur_nom'],
                        "prenom" => $livre['auteur_prenom'],
                        "Nationalite" => $livre['Nationalite'],
                    ]);
                    print "Auteur ajouté\n";

                    // Create a new book if it does not exist
                    $new_livre = Livre::create([
                        "id" => $livre['id'],
                        "titre" => $livre['titre'],
                        "anneePublication" => $livre['anneePublication'],
                        "category" => $livre['category'],
                        "description" => $livre['description'],
                        "maison_edition" => $livre['maison_edition'],
                        "nbr_page" => $livre['nbr_page'],
                        "etatcom" => 'acheté',
                        "langue" => $livre['langue']
                    ]);
                    print "Nouveau livre ajouté\n";

                    // Create a relationship between the author and the book
                    Ecrir::create([
                        "idAu" => $auteur->id,
                        "id" => $livre['id']
                    ]);
                    print "Relation Ecrire ajoutée\n";

                    // Loop through each photo path and create a photo record
                    foreach ($livre['photos'] as $photo) {
                        Photo::create([
                            "path" => $photo,
                            "isbn" => $livre['id'],
                        ]);
                        print "Nouvelle photo ajoutée\n";
                    }
                }
            } else {
                print "Ce livre n'est pas sélectionné\n";
            }
        }

        // Flush the session after processing
        $request->session()->flush();
        print "Session supprimée\n";

        return "Livres ajoutés avec succès";
    }

    public function store(Request $request)
    {
        // Validate the incoming request without flashing session data
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'auteur_nom' => 'required|string|max:255',
            'auteur_prenom' => 'required|string|max:255',
            'Nationalite' => 'required|string|max:255',
            'titre' => 'required|string|max:255',
            'anneePublication' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'etat' => 'required|string|max:255',
            'prix' => 'required|string|max:255',
            'nbr_page' => 'required|string|max:255',
            'maison_edition' => 'required|string|max:255',
            'langue' => 'required|string|max:255',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Handle photo uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                // Store each photo in the 'uploads' directory and get its path
                $path = $photo->store('uploads', 'public');
                $photoPaths[] = $path; // Add the path to the array
            }
        }
        

        // Get the existing proposed books from the session
        $livres = $request->session()->get('livresproposer', []);

        // Add the new book data along with photo paths
        $livreproposer = [
            'idt' => count($livres) + 1,
            'auteur_nom' => $validatedData['auteur_nom'],
            'auteur_prenom' => $validatedData['auteur_prenom'],
            'Nationalite' => $validatedData['Nationalite'],
            'id' => $validatedData['id'],
            'titre' => $validatedData['titre'],
            'anneePublication' => $validatedData['anneePublication'],
            'description' => $validatedData['description'],
            'category' => $validatedData['category'],
            'etat' => $validatedData['etat'],
            'prix' => $validatedData['prix'],
            'nbr_page' => $validatedData['nbr_page'],
            'langue' => $validatedData['langue'],
            'maison_edition' => $validatedData['maison_edition'],
            'photos' => $photoPaths,
        ];

        // Add the new proposed book to the session
        $livres[] = $livreproposer;
        $request->session()->put('livresproposer', $livres);

        // Return a JSON response indicating success
        return response()->json(['message' => 'Book proposed successfully!', 'data' => $livreproposer], 200);
    }

    public function showproposedlivres(Request $request)
    {
        return $request->session()->get('livresproposer', []);
    }

    public function Set_etatcom_livre(Request $request)
    {
        $livre = Livre::find($request->id);
        if ($livre) {
            if (in_array($request->type, ['archive', 'achete', 'vonde', 'a vendr'])) {
                $livre->etatcom = $request->type;
                $livre->save();
                return "fait";
            } else {
                return "Type d'état non valide";
            }
        } else {
            return "livre introuvable";
        }
    }

    /**
     * Display the specified resource.
     */
    public function showall(Request $request)
    {   
        $livres = DB::table('livres')
            ->where('etatcom', '=', 'a vendr')
            ->select('*')
            ->get();  
        return $livres;
    }

    // Les fonctions de recherche
    public function recherche(Request $request)
    {
        $typee = $request->type;

        if ($typee == "titre") {
            $livre = DB::table('livres')->select('*')->where('titre', '=', $request->value)->get();
            if ($livre->isNotEmpty()) {
                $photos = DB::table('photo')->select('path')->where('isbn', '=', $livre[0]->id)->get();
                $livre[0]->photos = $photos;
            }
            return $livre;

        } elseif ($typee == "isbn") {
            $livre = Livre::find($request->value);
            if ($livre) {
                $photos = DB::table('photo')->select('path')->where('isbn', '=', $livre->id)->get();
                $livre->photos = $photos;
            }
            return $livre;

        } elseif ($typee == "auteur") {
            if ($request->value && !$request->value2) {
                $livre = DB::table('livres as l')
                    ->join('auteurs as r', 'l.ida', '=', 'r.id')
                    ->select('l.*')
                    ->where('r.nom', 'like', '%' . $request->value . '%')
                    ->get();
            } elseif ($request->value2 && !$request->value) {
                $livre = DB::table('livres as l')
                    ->join('auteurs as r', 'l.ida', '=', 'r.id')
                    ->select('l.*')
                    ->where('r.prenom', 'like', '%' . $request->value2 . '%')
                    ->get();
            } elseif ($request->value && $request->value2) {
                $livre = DB::table('livres as l')
                    ->join('auteurs as r', 'l.ida', '=', 'r.id')
                    ->select('l.*')
                    ->where('r.nom', 'like', '%' . $request->value . '%')
                    ->where('r.prenom', 'like', '%' . $request->value2 . '%')
                    ->get();
            }

            if ($livre->isNotEmpty()) {
                foreach ($livre as $livr) {
                    $photos = DB::table('photo')->select('path')->where('isbn', '=', $livr->id)->get();
                    $livr->photos = $photos;
                }
            }
            return $livre;

        } elseif ($typee == "category") {
            $livre = DB::table('livres')->select('*')->where('category', '=', $request->value)->get();
            if ($livre->isNotEmpty()) {
                $photos = DB::table('photo')->select('path')->where('isbn', '=', $livre[0]->id)->get();
                $livre[0]->photos = $photos;
            }
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
    public function archiverLivre($id)
    {
        $livre = Livre::find($id);
        if ($livre) {
            $livre->etatcom = 'archivé';
            $livre->save();
            return response()->json(['message' => 'Le livre a été archivé avec succès']);
        } else {
            return response()->json(['error' => 'livre introuvable']);
        }
    }

    public function edite_livre(Request $request)
    { 
        $livre = Livre::find($request->id);

        if (!$livre) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        // Validate the request data (you can adjust this based on your validation rules)
        $validatedData = $request->validate([
            'titre' => 'string|max:255',
            'auteur' => 'string|max:255',
            'anneePublication' => 'string|max:255',
            'etat' => 'string|max:255',
            'prix' => 'string|max:255',
            'photo1' => 'string', // Example validation rules for an image upload
            'photo2' => 'string', // Example validation rules for the photo upload
        ]);

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

    public function test(Request $request)
    {
        $tet = $request->par;
        print $tet;
    }
}
