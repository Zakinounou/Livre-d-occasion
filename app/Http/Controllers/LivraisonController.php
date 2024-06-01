<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livraison;
use App\Models\Commande;
use App\Models\Livreur;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Exemplaire;
use App\Models\localisation;

class LivraisonController extends Controller
{
  
public function etablirLivraisons()
{
    // Check for the existence of commandes
    $commandes = Commande::all();
    if ($commandes->isEmpty()) {
        return 'No commandes found.';
    }
  //  return $commandes;

    // Check for available livreurs
    $livreursDisponibles = Livreur::where('disponible', 'oui')->get();
    if ($livreursDisponibles->isEmpty()) {
        return 'No available livreurs found.';
    }
  //  return $livreursDisponibles;

    // Group commandes by dist
    $groupedCommands = $commandes->groupBy('dist');
    //return $groupedCommands;
    // Initialize an array to hold the established livraisons
    $livraisons = [];
    $cr=1;
    foreach ($groupedCommands as $dist => $commandesGroup) {
        // Find an available livreur for this dist group
        $livreur = $livreursDisponibles->first();
    //return $livreur;
        if ($livreur) {
            foreach($commandesGroup as $commande){
            // Assign this livreur to all commandes in the group
            $livraison = new Livraison();
            $livraison->id=$cr;
            $livraison->idlivrr = $livreur->id; // Assign livreur ID to livraison
            $livraison->etat = 'en coures'; // Set livraison state
            $livraison->dist = $dist; // Set destination
            $livraison->idCom=$commande->id;
            $livraison->idloc = $commande->idloc;
            $livraison->save();


                
            }
           
        } else {
        print "no livreur disponible!";
        }
        $livreur->disponible = 'no';
        $livreur = $livreursDisponibles->shift();
        $livreur->save();

        $cr++;
    }
    

            // Update the livreur's status (assuming idlivrr is a foreign key)
   

            // Add the livraison to the list
            $livraisons[] = $livraison;
    return $livraisons;}

public function getAvailableHours(Request $request)
        {
            // Validate the request
            $request->validate([
                'date_livr' => 'required|date_format:Y-m-d',
            ]);
    
            $date = $request->date_livr;  
    
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

public function setChosenHour(Request $request)
    {
       // Validate the request
       $request->validate([
        'date' => 'required|date|after_or_equal:today',
        'hour' => 'required|date_format:H:i:s'
    ]);

    // Get the authenticated user ID
    $userId = auth()->id();

    // Parse the date and hour
    $date = Carbon::parse($request->date);
    $hour = $request->hour;

    // Check if the date is a Friday (Carbon::FRIDAY == 5)
    if ($date->dayOfWeek == Carbon::FRIDAY) {
        return response()->json([
            'message' => 'No deliveries are available on Fridays.'
        ], 400);
    }

    // Check if the hour is within business hours
    $businessStartHour = 8;
    $businessEndHour = 16;
    $selectedHour = Carbon::createFromTimeString($hour);
    if ($selectedHour->hour < $businessStartHour || $selectedHour->hour >= $businessEndHour) {
        return response()->json([
            'message' => 'Selected hour is outside of business hours.'
        ], 400);
    }

    // Check if the slot is already taken
    $existingDelivery = Livraison::whereDate('datelivr', $date->toDateString())
                                ->whereTime('heure', $hour)
                                ->first();

    if ($existingDelivery) {
        return response()->json([
            'message' => 'Selected slot is already taken.'
        ], 400);
    }
    // Find the livraison record to update
  // Find the commande record for the authenticated user
  $commande = Commande::where('idAch', $userId)->first();

  if (!$commande) {
      return response()->json([
          'message' => 'Commande not found for authenticated user.'
      ], 404);
  }

  // Find the livraison record to update using the idcom from the commande
  $livraison = Livraison::where('idcom', $commande->id)->first();

    if (!$livraison) {
        return response()->json([
            'message' => 'Livraison not found.'
        ], 404);
    }

    // Update the livraison record
    $livraison->datelivr = $date->toDateString();
    $livraison->heure = $hour;
    $livraison->save();

    return response()->json([
        'message' => 'Livraison updated successfully',
        'livraison' => $livraison
    ]);
}
public function voirLivraisonsAssignees()
    {
        $userId = auth()->id(); // Obtenir l'ID du livreur authentifié

        $livraisons = Livraison::where('idlivrr', $userId)->get();

        return response()->json([
            'livraisons' => $livraisons
        ]);
    }

    // Fonction pour mettre à jour le statut de la livraison (y compris marquer comme livrée)
public function mettreAJourStatutLivraison(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|string'
        ]);

        $livraison = Livraison::find($id);

        if (!$livraison) {
            return response()->json([
                'message' => 'Livraison non trouvée.'
            ], 404);
        }

        $livraison->etat = $request->statut;
        $livraison->save();

        return response()->json([
            'message' => 'Statut de la livraison mis à jour avec succès',
            'livraison' => $livraison
        ]);
    }

    // Fonction pour voir les détails de la livraison avec informations supplémentaires
public function voirDetailsLivraison($id)
    {
        $livraisons = Livraison::where('id',$id)->get();

        if (!$livraisons) {
            return response()->json([
                'message' => 'Livraison non trouvée.'
            ], 404);
        }
            
            $detailsLivraisons = [];
        
            foreach ($livraisons as $livraison) {
                // Récupérer les informations supplémentaires
                $commande = Commande::find($livraison['idcom']);
                $client = User::find($commande->idAch);
                $localisation=localisation::find($livraison['idloc']);
                $detailsLivraisons[] = [
                    'date livraison'=>$livraison['datelivr'],
                    'Heure  de livraison'=> $livraison['heure'],
                    'nom_client' => $client->nom,
                    'numero_telephone' => $client->numeroDetel,
                    'distination' => $livraison['dist'],
                    'localisation'=>$localisation->direction,
                    'montant_total' => $commande->montantTotal
                ];
            }
        
            return response()->json([
                'livraisons' => $detailsLivraisons
            ]);
        }
        

    // Fonction pour mettre à jour le planning de la livraison
public function mettreAJourPlanningLivraison(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'heure' => 'required|date_format:H:i:s'
        ]);

        $date = Carbon::parse($request->date);
        $heure = $request->heure;

        // Vérifier si la date est un vendredi (Carbon::FRIDAY == 5)
        if ($date->dayOfWeek == Carbon::FRIDAY) {
            return response()->json([
                'message' => 'Aucune livraison n\'est disponible le vendredi.'
            ], 400);
        }

        // Vérifier si l'heure est dans les heures de travail
        $heureDebut = 8;
        $heureFin = 16;
        $heureSelectionnee = Carbon::createFromTimeString($heure);
        if ($heureSelectionnee->hour < $heureDebut || $heureSelectionnee->hour >= $heureFin) {
            return response()->json([
                'message' => 'L\'heure sélectionnée est en dehors des heures de travail.'
            ], 400);
        }

        // Vérifier si le créneau est déjà pris
        $livraisonExistante = Livraison::whereDate('datelivr', $date->toDateString())
                                       ->whereTime('heure', $heure)
                                       ->first();

        if ($livraisonExistante) {
            return response()->json([
                'message' => 'Le créneau sélectionné est déjà pris.'
            ], 400);
        }

        $livraison = Livraison::find($id);

        if (!$livraison) {
            return response()->json([
                'message' => 'Livraison non trouvée.'
            ], 404);
        }

        $livraison->datelivr = $date->toDateString();
        $livraison->heure = $heure;
        $livraison->save();

        return response()->json([
            'message' => 'Planning de la livraison mis à jour avec succès',
            'livraison' => $livraison
        ]);
    }

    // Optionnel : Fonction pour se localiser au lieu de livraison
public function seLocaliserAuLieuDeLivraison($id)
    {
        $livraison = Livraison::find($id);

        if (!$livraison) {
            return response()->json([
                'message' => 'Livraison non trouvée.'
            ], 404);
        }

        // Exemple : Mettre à jour le statut de check-in ou l'heure
        $livraison->check_in_time = Carbon::now();
        $livraison->save();

        return response()->json([
            'message' => 'Localisé au lieu de livraison',
            'livraison' => $livraison
        ]);
    }
}
