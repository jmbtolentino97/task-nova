<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware("auth:sanctum")->group(function() {
    Route::apiResource("projects", ProjectController::class);
});
