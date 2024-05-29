<?php

namespace App\Http\Controllers;

use App\Models\Panier; // Import the Panier model
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class panierController extends Controller
{

public function ajoutouPanier(Request $request)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'idEx' => 'required|int', // Assuming idEx is required and an integer
    ]);

    // Get the authenticated user's ID
    $userId = auth()->id();

    // Check if the user already has an entry in the panier table
    $existingPanier = Panier::where('idC', $userId)->first();

    if ($existingPanier) {
        // User already has an entry in panier, get the idpanier
        $idpanier = $existingPanier->idPanier;
    } else {
        // User doesn't have an entry in panier, get the max idpanier
        $maxIdPanier = Panier::max('idpanier');
        $idpanier = $maxIdPanier ? $maxIdPanier + 1 : 1; // Increment max idpanier or start from 1 if no records
    }

    // Now, insert into the panier table
    Panier::create([
        'idPanier' => $idpanier,
        'idEx' => $validatedData['idEx'],
        'idC' => $userId,
    ]);

    // Optionally, return a response
    return response()->json(['message' => 'Item added to panier successfully'], 200);
}

public function deleteFromPanier(Request $request)
{
    
    $validatedData = $request->validate([
        'idEx' => 'required|int', // Assuming idExamplaire is required and an integer
    ]);
    $userId = auth()->id();
    $panierItem = Panier::where('idC', $userId)
                        ->where('idEx', $validatedData['idEx'])
                        ->first();
    if ($panierItem) {

        Panier::where('idPanier', $panierItem->idPanier)->delete();

        return response()->json(['message' => 'Item removed from panier successfully'], 200);
    } else {
        return response()->json(['message' => 'Item not found in panier'], 404);
    }

}
}