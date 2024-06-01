<?php

use App\Http\Controllers\adminController;
use Illuminate\Http\Request;
use App\Http\Controllers\livreController;
use App\Http\Controllers\achatController;
use App\Http\Controllers\Api\NewPasswordController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\localisationController;
use App\Http\Controllers\panierController;
use App\Http\Controllers\promotionController;
use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authantication users
Route::post('/registere',userController::class.'@registere');
Route::get('/logine',userController::class.'@logine');
Route::post('/archiver',[adminController::class,'archiverClient']);

//livre
Route::get('/showproposedlivres',[livreController::class,'showProposedlivres'])->middleware('api-session');
Route::middleware('api-session','auth:sanctum')->resource('/proposer', livreController::class);
Route::get('/exposition',[livreController::class,'showall']);
Route::post('/edite_livre',[livreController::class,'edite_livre']);
Route::get('/recherche',[livreController::class,'recherche']);
Route::post('/valider',[livreController::class,'create'])->middleware('api-session');
Route::put('/remove_livre/{id}',[livreController::class,'archiverLivre']);
Route::get('/etatcom',[livreController::class,'set_etatcom_livre']);
Route::get('/bestvente',[userController::class,'best_vente']);
Route::get('/derniersLivresAjoutes',[userController::class,'derniersLivresAjoutes']);


//panier
Route::post('/ajoutouPanier',[panierController::class,'ajoutouPanier'])->middleware('auth:sanctum');
Route::delete('/deleteFromPanier', [panierController::class, 'deleteFromPanier'])->middleware('auth:sanctum');
Route::get('/getAllPaniers',[panierController::class,'getAllPaniers'])->middleware('auth:sanctum');
//historique  d'achats
Route::get('/history',[achatController::class,'history'])->middleware('auth:sanctum');

//promotion
Route::post('/ajouterpromotion',[promotionController::class,'ajouterpromotion']);
Route::post('/effectuerPromotion',[promotionController::class,'effectuerPromotion']);
Route::delete('/removePromotion/{id}',[promotionController::class,'removePromotion']);

//Route::delete('',[::class,'']);
//Route::post('/test',[livreController::class,'test']);

//profil
Route::post('photo_Profile',[userController::class,'photo_Profile'])->middleware('auth:sanctum');
Route::post('modifier_photo_Profile',[userController::class,'photo_Profile'])->middleware('auth:sanctum');
Route::post('new_password', [NewPasswordController::class, 'forgotPassword']);
Route::post('rest_password', [NewPasswordController::class, 'reset']);
Route::put('/modifier_address', [userController::class,'updateaddress'])->middleware('auth:sanctum');
Route::put('/modifier_numeroTEl', [userController::class,'updatemobile'])->middleware('auth:sanctum');

//localisation
Route::middleware('auth:sanctum')->Resource('locations', localisationController::class);
Route::middleware('auth:sanctum')->get('userlocations', [localisationController::class, 'show']);


//commande
Route::middleware('api-session','auth:sanctum')->resource('/proposercommande', userController::class);
Route::get('/showrequestedCommande',[userController::class,'showRequestCommande'])->middleware('api-session');
Route::post('/etablireCommandes',[userController::class,'etablireCommandes'])->middleware('api-session');

//livraison

Route::get('/etablirLivraisons',[LivraisonController::class,'etablirLivraisons']);  
Route::get('/AvailableHours',[LivraisonController::class,'getAvailableHours']);  
Route::post('/set-chosen-hour', [LivraisonController::class, 'setChosenHour'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/livraisons-assignees', [LivraisonController::class, 'voirLivraisonsAssignees']);
    Route::post('/mettre-a-jour-statut-livraison/{id}', [LivraisonController::class, 'mettreAJourStatutLivraison']);
    Route::get('/voir-details-livraison/{id}', [LivraisonController::class, 'voirDetailsLivraison']);
    Route::post('/mettre-a-jour-planning-livraison/{id}', [LivraisonController::class, 'mettreAJourPlanningLivraison']);
    Route::post('/se-localiser-lieu-livraison/{id}', [LivraisonController::class, 'seLocaliserAuLieuDeLivraison']);
});