<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;



/*

Route::get('/', function () {
    return view('welcome');
});

 */
Volt::route('/', 'clientes.login')->name('clientes.login');
Volt::route('/login', 'clientes.login')->name('clientes.login');

Route::view('/dashboard', 'dashboard', ['activeMenu' => 'dashboard'])->name('dashboard');
Route::view('/estadisticas', 'dashboard', ['activeMenu' => 'dashboard'])->name('clientes.estadisticas');
Route::view('/comprobantes', 'dashboard', ['activeMenu' => 'comprobantes'])->name('clientes.comprobantes');
Route::view('/ordenes/ingresos', 'dashboard', ['activeMenu' => 'ordenesingreso'])->name('clientes.ordenes.ingresos');
Route::view('/ordenes/salidas', 'dashboard', ['activeMenu' => 'ordenessalida'])->name('clientes.ordenes.salidas');
Route::view('/ordenes/transportes', 'dashboard', ['activeMenu' => 'ordenestransporte'])->name('clientes.ordenes.transportes');
Route::view('/ordenes/servicios', 'dashboard', ['activeMenu' => 'ordenesservicio'])->name('clientes.ordenes.servicios');
Route::view('/ordenes/alquileres', 'dashboard', ['activeMenu' => 'ordenesalquiler'])->name('clientes.ordenes.alquileres');
Route::view('/perfil', 'dashboard', ['activeMenu' => 'perfil'])->name('clientes.perfil-usuario');


Volt::route('/plantillas', 'plantillas')->name('plantillas');




Route::get('/salir', [\App\Http\Controllers\PerfilController::class, 'CerrarSesion'])->name('salir');
