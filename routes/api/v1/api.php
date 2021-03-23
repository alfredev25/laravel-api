<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PracticalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix'=>'auth'],function(){
    Route::post('login', [PracticalController::class, 'login'])->middleware("throttle:10,2");
    Route::post('register',[PracticalController::class, 'register']);

    Route::group(['middleware'=> 'auth:api'],
        function(){
            Route::post('orders', [PracticalController::class, 'orders']);
        });
});