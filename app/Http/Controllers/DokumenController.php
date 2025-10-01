<?php

namespace App\Http\Controllers;


use App\Models\Dokumen;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function index()
    {
        $dokumen = Dokumen::all();
        return view('pages.tables', compact('dokumen'));
    }
}
