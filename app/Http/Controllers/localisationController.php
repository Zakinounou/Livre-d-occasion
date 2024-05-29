<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\localisation;
use App\Models\User;

class localisationController extends Controller
{
    public function index()
    {
        // Return a list of all localisations

        
        $localisations = localisation::all();
        return response()->json($localisations);
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'direction' => 'required|url',
        ]);

        // Retrieve the user
        $userID=auth()->id();
        $user = User::find($userID);

        // Create or update the localisation for the user
        $localisation = localisation::Create([
            'idAch' => $user->id,
            'direction' => $validatedData['direction']]
        );

        return response()->json(['message' => 'localisation assigned successfully', 'localisation' => $localisation]);
    }

    public function show()
    {
        // Show a specific localisation
        $userID=auth()->id();
        $user = User::find($userID);

        $localisation = localisation::where('idAch', $userID)->get();
        if (!$localisation) {
            return response()->json(['error' => 'localisation not found'], 404);
        }
        return response()->json($localisation);
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            
            'direction' => 'required|url',
        ]);
        
        // Retrieve the localisation
        $localisation = localisation::find($id);

        if (!$localisation) {
            return response()->json(['error' => 'localisation not found'], 404);
        }

        // Update the localisation
        $localisation->update(['direction' => $validatedData['direction']]);

        return response()->json(['message' => 'localisation updated successfully', 'localisation' => $localisation]);
    }

    public function destroy($id)
    {
        // Retrieve the localisation
        $localisation = localisation::find($id);

        if (!$localisation) {
            return response()->json(['error' => 'localisation not found'], 404);
        }

        // Delete the localisation
        $localisation->delete();

        return response()->json(['message' => 'localisation deleted successfully']);
    }
}
