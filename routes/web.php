<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;



/*

Route::get('/', function () {
    return view('welcome');
});

 */
Volt::route('/', 'clientes.login')->name('clientes.login');
Route::get('dashboard', function () { return view('dashboard'); })->name('dashboard');
Volt::route('/login', 'clientes.login')->name('clientes.login');
Volt::route('/estadisticas', 'clientes.estadisticas')->name('clientes.estadisticas');
Volt::route('/comprobantes', 'clientes.comprobantes')->name('clientes.comprobantes');
Volt::route('/perfil', 'clientes.perfil-usuario')->name('clientes.perfil-usuario');


Volt::route('/plantillas', 'plantillas')->name('plantillas');




Route::get('/salir', [\App\Http\Controllers\PerfilController::class, 'CerrarSesion'])->name('salir');
