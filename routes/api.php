<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Enma School
|--------------------------------------------------------------------------
|
| Ce fichier définit les routes API de l'application Enma School.
| Toutes les routes définies ici sont automatiquement préfixées par 'api'.
|
| IMPORTANT : Ce fichier contient uniquement les routes techniques de base.
| Les routes métier seront ajoutées lors du développement fonctionnel.
|
*/

// Route technique pour l'authentification (fournie par Laravel Sanctum)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Routes métier futures
|--------------------------------------------------------------------------
|
| Les routes suivantes seront ajoutées lors du développement :
|
| - Gestion des étudiants : /students
| - Gestion des classes : /classrooms
| - Gestion des notes : /grades
| - Gestion des présences : /attendances
| - Gestion des utilisateurs : /users
|
*/
