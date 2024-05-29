<?php

use App\Http\Controllers\adminController;
use Illuminate\Http\Request;
use App\Http\Controllers\livreController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\achatController;
use App\Http\Controllers\Api\NewPasswordController;
use App\Http\Controllers\localisationController;
use App\Http\Controllers\panierController;
use App\Http\Controllers\promotionController;
use App\Http\Controllers\userController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authantication
Route::post('/registere',userController::class.'@registere');
Route::get('/logine',userController::class.'@logine');
Route::get('/showproposedlivres',[livreController::class,'showProposedlivres'])->middleware('api-session');


Route::middleware('api-session','auth:sanctum')->resource('/proposer', livreController::class);
Route::get('/exposition',[livreController::class,'showall']);
Route::post('/edite_livre',[livreController::class,'edite_livre']);
Route::get('/recherche',[livreController::class,'recherche']);
Route::post('/valider',[livreController::class,'create'])->middleware('api-session');
Route::get('/admin',[adminController::class,]);

Route::get('/archiver',[adminController::class,'archiverClient']);
Route::put('/remove_livre/{id}',[livreController::class,'archiverLivre']);
Route::get('/etatcom',[livreController::class,'set_etatcom_livre']);
Route::post('/test',[livreController::class,'test']);

Route::post('/photo_profil',[livreController::class,'uploadImages']);
Route::get('/bestvente',[userController::class,'best_vente']);
Route::get('/derniersLivresAjoutes',[userController::class,'derniersLivresAjoutes']);

Route::post('/ajoutouPanier',[panierController::class,'ajoutouPanier'])->middleware('auth:sanctum');
Route::delete('/deleteFromPanier', [panierController::class, 'deleteFromPanier'])->middleware('auth:sanctum');

Route::get('/history',[achatController::class,'history'])->middleware('auth:sanctum');

Route::post('/ajouterpromotion',[promotionController::class,'ajouterpromotion']);
Route::post('/effectuerPromotion',[promotionController::class,'effectuerPromotion']);
Route::delete('/removePromotion/{id}',[promotionController::class,'removePromotion']);

//Route::delete('',[::class,'']);
Route::post('photo_Profile',[userController::class,'photo_Profile'])->middleware('auth:sanctum');
Route::post('modifier_photo_Profile',[userController::class,'photo_Profile'])->middleware('auth:sanctum');


Route::post('new_password', [NewPasswordController::class, 'forgotPassword']);
Route::post('rest_password', [NewPasswordController::class, 'reset']);
Route::put('/modifier_address', [userController::class,'updateaddress'])->middleware('auth:sanctum');
Route::put('/modifier_numeroTEl', [userController::class,'updatemobile'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->Resource('locations', localisationController::class);
Route::middleware('auth:sanctum')->get('userlocations', [localisationController::class, 'show']);

