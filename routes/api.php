<?php

use App\Http\Controllers\adminController;
use Illuminate\Http\Request;
use App\Http\Controllers\livreController;
use App\Http\Controllers\userController;
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
Route::post('/changer',[livreController::class,'change']);
Route::get('/recherche',[livreController::class,'recherche']);
Route::post('/valider',[livreController::class,'create'])->middleware('api-session');
Route::get('/admin',[adminController::class,]);

Route::get('/archiver',[adminController::class,'archiverClient']);
Route::get('/supprimer_livre',[livreController::class,'supprimerLivre']);
Route::get('/etatcom',[livreController::class,'set_etatcom_livre']);
Route::post('/test',[livreController::class,'test']);

Route::post('/photo_profil',[livreController::class,'uploadImages']);




