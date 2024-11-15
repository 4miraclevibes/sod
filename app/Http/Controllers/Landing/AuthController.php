<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function login()
    {
        if(Auth::check()) {
            return redirect()->route('home');
        }
        return view('pages.landing.auth.login');
    }

    public function register()
    {
        if(Auth::check()) {
            return redirect()->route('home');
        }
        return view('pages.landing.auth.register');
    }
}
