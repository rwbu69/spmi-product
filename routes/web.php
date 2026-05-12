<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/settings.php';
require __DIR__.'/spmi.php';
