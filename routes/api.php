<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::get('/health', function () {
        return response()->json(['status' => 'OK']);
    });
    Route::get('/metrics', function () {
        \ = storage_path('app/metrics.json');
        if (!file_exists(\)) {
            file_put_contents(\, json_encode(['today' => 0, 'week' => 0, 'total' => 0]));
        }
        return response()->json(json_decode(file_get_contents(\)));
    });
});
