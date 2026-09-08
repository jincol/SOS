<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;



class PerfilController extends Controller
{


    public function CerrarSesion(Request $request)
    {

        Session::forget(['user_data', 'user_id', 'authenticated']);

        Session::flush();

        //dd($request);

       $request->session()->regenerateToken();



        return redirect('/login')->with('success', 'Has cerrado sesión correctamente');
    }//




}
