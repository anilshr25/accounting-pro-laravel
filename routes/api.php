<?php

use Illuminate\Support\Facades\Route;

Route::get('/health-check', fn () => response()->json(['status' => 'OK']));
