<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index']);

// Postman collection downloads
Route::get('/downloads/{file}', function (string $file) {
    $allowedFiles = [
        'Laravel_Stripe_SaaS_Billing_API.postman_collection.json',
        'Laravel_Stripe_SaaS_Billing_API.postman_environment.json',
    ];

    if (!in_array($file, $allowedFiles)) {
        abort(404);
    }

    $path = public_path("downloads/{$file}");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
})->where('file', '.*');

require __DIR__.'/admin.php';
