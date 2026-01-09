<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::middleware(['web'])->group(function ($route): void {
    include base_path('routes/tenant.php');
});
