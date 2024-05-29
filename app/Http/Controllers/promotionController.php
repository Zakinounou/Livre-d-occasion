<?php

namespace App\Http\Controllers;
use App\Models\promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\livre;
class promotionController extends Controller
{

    public function ajouterpromotion(Request $request){
        
        $request->validate([
            'pourcentage' => 'required|numeric|min:0|max:100',
            'dat_debut' => 'required|date',
            'dat_fin' => 'required|date|after_or_equal:dat_debut',
           ]);
        $promotion = Promotion::create([
            'pourcentage' => $request->pourcentage,
            'dat_debut' => $request->dat_debut,
            'dat_fin' => $request->dat_fin,
        ]);
        return $promotion;
}
    public function effectuerPromotion(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'promotion_id' => 'required'
        ]);
        $promotion = Promotion::where('id', $request->promotion_id)->first();
        if (!$promotion) {
            return response()->json(['error' => 'promotion Expired'], 404);
        }

         $livre = Livre::where('titre', $request->titre)->first();
        if (!$livre) {
            return response()->json(['error' => 'Livre not found'], 404);
        }

        $livre->id_pro = $request->promotion_id;
        $livre->save();
        return response()->json(['message' => 'Promotion assigned successfully', 'data' => $livre], 200);
    }
    public function removePromotion($id){
        $promotion = Promotion::find($id);
    if (!$promotion) {
            return response()->json(['error' => 'Promotion not found'], 404);
        }
        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted successfully'], 200);
    }


    }   




