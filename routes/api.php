<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'namespace' => 'App\Http\Controllers'
], function ($router) {
    Route::patch('setting', 'OverTimeController@setting');
    Route::post('employee', 'OverTimeController@saveEmployee');
    Route::post('overtime', 'OverTimeController@saveOvertime');
    Route::get('overtime/calculate', 'OverTimeController@overTimeCalculate');
});
