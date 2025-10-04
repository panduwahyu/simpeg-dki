<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image; // Laravel 11 + Intervention Image v3

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil
     */
    public function create()
    {
        return view('pages.profile');
    }
}
