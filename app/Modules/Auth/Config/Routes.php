<?php

use Illuminate\Support\Facades\Route;

/*
| Tampilan autentikasi (Fase 0).
| Handler POST (login/register) ditambahkan saat logika autentikasi diimplementasikan.
*/

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
