<?php

use Illuminate\Support\Facades\Route;

Route::get('/alternative-login', \Filament\Pages\Auth\Login::class)->name('login');
