<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelUploadController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/progress/{fileName}/{timestamp}', [ExcelUploadController::class, 'getProgress']);
