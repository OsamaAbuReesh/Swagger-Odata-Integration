<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ODataProductController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/odata/products', [ODataProductController::class, 'index']);
Route::post('/odata/products', [ODataProductController::class, 'store']);
Route::put('/odata/products/{id}', [ODataProductController::class, 'update']);
Route::delete('/odata/products/{id}', [ODataProductController::class, 'destroy']);
