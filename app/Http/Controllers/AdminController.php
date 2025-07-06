<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Logica per la vista dell'admin

        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Access denied');
        }
        return view('admin.index');
    }
}
