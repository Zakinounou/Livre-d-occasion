<?php

namespace App\Http\Controllers;

use App\Models\Panier; // Import the Panier model
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\CssSelector\XPath\Extension\FunctionExtension;

class PanierController extends Controller
{
    public function ajoutouPanier(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'idEx' => 'required|int', // Assuming id is required and an integer
        ]);

        // Get the authenticated user's ID
        $userId = auth()->id();

        // Check if the user already has an entry in the panier table
        $existingPanier = Panier::where('idC', $userId)->first();

        if ($existingPanier) {
            // User already has an entry in panier, get the id
            $id = $existingPanier->id;

        } else {
            // User doesn't have an entry in panier, get the max id
            $maxId = Panier::max('id');
            //return $maxId;
            $id = $maxId ? $maxId + 1 : 1; 
            //return $id;// Increment max id or start from 1 if no records
        }
        //return $id;
        // Now, insert into the panier table
        Panier::create([
            'id' => $id,
            'idEx' => $validatedData['idEx'],
            'idC' => $userId,
        ]);

        // Optionally, return a response
        return response()->json(['message' => 'Item added to panier successfully'], 200);
    }

    public function deleteFromPanier(Request $request)
    {
        $validatedData = $request->validate([
            'idEx' => 'required|int', // Assuming id is required and an integer
        ]);

        $userId = auth()->id();
        $panierItem = Panier::where('idC', $userId)
                            ->where('idEx', $validatedData['idEx'])
                            ->first();
        if ($panierItem) {
            Panier::where('id', $panierItem->id)->delete();

            return response()->json(['message' => 'Item removed from panier successfully'], 200);
        } else {
            return response()->json(['message' => 'Item not found in panier'], 404);
        }
    }

    public function getAllPaniers()
    {
        // Retrieve all records from the Panier table
        $paniers = Panier::all();

        // Return the records as a JSON response
        return response()->json($paniers, 200);
    }
}
