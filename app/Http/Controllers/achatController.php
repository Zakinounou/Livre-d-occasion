<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Achat;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AchatController extends Controller
{
    public function history(Request $request)
    { 
        // Assuming the user is authenticated and you are using Sanctum for API authentication
        $userId = auth()->id();
        
        // Use query builder to replicate the SQL query
        $results = DB::table('exemplaire')
            ->join('livres', 'exemplaire.isbn', '=', 'livres.id')
            ->join('achat', 'exemplaire.id', '=', 'achat.idEx')
            ->where('achat.idAch', $userId)
            ->select('livres.titre', 'exemplaire.etat', 'exemplaire.prix', 'achat.date_Achat as date_achat')
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => "You haven't bought anything yet"], 200);
        }

        // Return the results as a JSON response
        return response()->json($results, 200);
    }
}
