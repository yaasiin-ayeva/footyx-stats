<?php

use App\Http\Controllers\AdminFileController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayerFileController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VarGroupController;
use App\Models\Variable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

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

Route::middleware('auth')->group(function () {
    Route::get('/change_picture', [UserController::class, 'get_change_picture'])->name('get_change_picture');
    Route::post('/change_picture', [UserController::class, 'change_picture'])->name('change_picture');
    Route::post('/remove_picture', [UserController::class, 'remove_picture'])->name('remove_picture');

    Route::get('/change_pwd', [UserController::class, 'get_change_pwd'])->name('get_change_pwd');
    Route::post('/change_pwd', [UserController::class, 'change_pwd'])->name('change_pwd');

    Route::get('/dashboard', [UserController::class, 'get_dashboard'])->name('dashboard'); // NI

    // Route::prefix('notifications')->group(function () {
    //     Route::get('/', [NotificationController::class, 'index'])->name('not.index');
    //     Route::get('/show/{id}', [NotificationController::class, 'show'])->name('not.show');
    // });

    Route::prefix('players')->group(function () {
        Route::get('/', [PlayerController::class, 'index'])->name('ply.index');
        Route::post('/list', [PlayerController::class, 'ply_list'])->name('ply.list');
        Route::post('/delete/{id}', [PlayerController::class, 'delete'])->name('ply.delete');
    });

    Route::prefix('leagues')->group(function () {
        Route::get('/', [LeagueController::class, 'index'])->name('lg.index');
        Route::post('/list', [LeagueController::class, 'lg_list'])->name('lg.list');
        
        Route::get('/all', [LeagueController::class, 'all'])->name('lg.all');
        Route::post('/all_list', [LeagueController::class, 'lg_all_list'])->name('lg.all_list');
        
        Route::post('/store', [LeagueController::class, 'store'])->name('lg.store');
        Route::post('/update/{id}', [LeagueController::class, 'update'])->name('lg.update');
        Route::post('/delete/{id}', [LeagueController::class, 'delete'])->name('lg.delete');
    });

    Route::prefix('var_groups')->group(function () {
        Route::get('/', [VarGroupController::class, 'index'])->name('vg.index');
        Route::post('/list', [VarGroupController::class, 'vg_list'])->name('vg.list');
        
        Route::post('/store', [VarGroupController::class, 'store'])->name('vg.store');
        Route::post('/update/{id}', [VarGroupController::class, 'update'])->name('vg.update');
        Route::post('/dissolve/{id}', [VarGroupController::class, 'delete'])->name('vg.dissolve');
    });

    Route::prefix('admin_files')->group(function () {
        Route::get('/', [AdminFileController::class, 'index'])->name('adf.index');
        Route::post('/list', [AdminFileController::class, 'adf_list'])->name('adf.list');

        Route::get('/all', [AdminFileController::class, 'all'])->name('adf.all');
        Route::post('/all_list', [AdminFileController::class, 'adf_all_list'])->name('adf.all_list');
        
        Route::post('/load', [AdminFileController::class, 'load'])->name('adf.load');
        
        Route::get('/matches', [AdminFileController::class, 'get_matches'])->name('adf.get_matches');
        Route::post('/matches_list', [AdminFileController::class, 'matches_list'])->name('adf.matches_list');
        
        Route::post('/delete/{id}', [AdminFileController::class, 'delete'])->name('adf.delete');
    });

    Route::prefix('countries')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('cnt.index');
        Route::post('/list', [CountryController::class, 'cnt_list'])->name('cnt.list');
        Route::post('/leagues', [CountryController::class, 'get_leagues'])->name('cnt.get_leagues');
        Route::post('/empty/{id}', [CountryController::class, 'empty'])->name('cnt.empty');
    });

    Route::prefix('player_files')->group(function () {
        Route::get('/', [PlayerFileController::class, 'index'])->name('plyf.index');
        Route::post('/list', [PlayerFileController::class, 'plyf_list'])->name('plyf.list');
        
        Route::post('/load', [PlayerFileController::class, 'load'])->name('plyf.load');

        Route::get('/matches', [PlayerFileController::class, 'get_matches'])->name('plyf.get_matches');
        Route::post('/matches_list', [PlayerFileController::class, 'matches_list'])->name('plyf.matches_list');
        
        Route::post('/delete/{id}', [PlayerFileController::class, 'delete'])->name('plyf.delete');

        Route::get('/select_teams', [PlayerFileController::class, 'get_select_teams'])->name('plyf.get_select_teams');
        Route::get('/predict', [PlayerFileController::class, 'get_predict'])->name('plyf.get_predict'); // NI
        Route::post('/search', [PlayerFileController::class, 'search'])->name('plyf.search');

        Route::get('/schedules', [PlayerFileController::class, 'get_schedules'])->name('plyf.get_schedules');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/', [UserController::class, 'get_login'])->name('index');
    Route::get('/login', [UserController::class, 'get_login'])->name('get_login');
    Route::post('/login', [UserController::class, 'login'])->name('login');

    Route::get('/register', [UserController::class, 'get_register'])->name('get_register');
    Route::post('/register', [UserController::class, 'register'])->name('register');

    // Route::post('/send_signin_otp', [StaffController::class, 'send_signin_otp'])->name('send_signin_otp');

    Route::get('/password/reset/email', [UserController::class, 'get_password_reset_email_form'])->name('get_password_reset_email_form');
    Route::post('/password/reset/email', [UserController::class, 'send_password_reset_email'])->name('send_password_reset_email');

    Route::get('/password/reset', [UserController::class, 'get_password_reset_form'])->name('get_password_reset_form');
    Route::post('/password/reset', [UserController::class, 'reset_password'])->name('reset_password');
});

Route::middleware('web')->group(function () {
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    // Route::post('/validate_otp', [StaffController::class, 'validate_otp'])->name('validate_otp');
});

Route::prefix('test')->group(function () {
    Route::get('/info', [TestController::class, 'info']);

    Route::get('/bcrypt/{text}', [TestController::class, 'bcrypt']);

    Route::get('/format', [TestController::class, 'format']);

    Route::get('/load_zeepay_files', [TestController::class, 'load_zeepay_files']);

    Route::get('/compute_summary', [TestController::class, 'compute_summary']);
    
    Route::get('/get_unref', [TestController::class, 'get_unref']);

    Route::get('/test_files', [TestController::class, 'test_files']);

    Route::get('/zeepay_1', [TestController::class, 'zeepay_1']);
});