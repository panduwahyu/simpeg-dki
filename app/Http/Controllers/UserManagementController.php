<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::all(); // ambil semua user
        return view('pages.laravel-examples.user-management', compact('users'));
    }
}
