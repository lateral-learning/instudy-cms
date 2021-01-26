<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/cmsUploadStudy', [App\Http\Controllers\cmsUploadFileController::class, 'index']);

Route::get('/cmsAddUser', [App\Http\Controllers\cmsAddUserController::class, 'index']);

Route::post('/uploadStudy', [App\Http\Controllers\UploadFileController::class, 'index']);

Route::post('/addUser', [App\Http\Controllers\AddUserController::class, 'index']);

Route::get('/resetPassword', [App\Http\Controllers\ResetPasswordController::class, 'index']);

Route::any('/success', function () {
    return view('success');
})->name("success");

/* DECOMMENTARE PER ACCEDERE AL CONFIG INIZIALE
Route::get('/initial_config', function () {
    $migration = Artisan::call('migrate', []);
    $seed = Artisan::call('db:seed', []);
});
*/
