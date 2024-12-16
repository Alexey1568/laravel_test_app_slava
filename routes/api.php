<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelUploadController;
use App\Http\Controllers\ExcelViewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/upload-excel', [ExcelUploadController::class, 'upload'])->middleware('auth.basic');
Route::get('/excel/data', [ExcelViewController::class, 'index']);
Route::get('/excel/data/{date}', [ExcelViewController::class, 'getByDate']);
    
    