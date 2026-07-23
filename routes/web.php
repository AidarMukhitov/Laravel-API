<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store'])->middleware(\App\Http\Middleware\RateLimit::class);
    Route::get('/health', function () {
        return response()->json(['status' => 'OK']);
    });
    Route::get('/metrics', function () {
        $filePath = storage_path('app/metrics.json');
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode(['today' => 0, 'week' => 0, 'total' => 0]));
        }
        return response()->json(json_decode(file_get_contents($filePath)));
    });
});

Route::get('/api/docs', function () {
    return view('l5-swagger::index');
})->name('l5-swagger.api.docs');