<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public $username;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->username = Auth::user()->username;
            return $next($request);
        });
    }

    public function index()
    {
        return view('home', [
            // Returned as collection
            'username' => $this->username,
        ]);
    }
}
