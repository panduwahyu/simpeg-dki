<?php

namespace App\Http\Controllers;


use App\Models\Dokumen;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function index()
    {
        // $dokumens = Dokumen::orderBy('tanggal_unggah', 'desc')->get();
        $dokumens = Dokumen::with(['jenisDokumen', 'periode'])
                           ->orderBy('tanggal_unggah', 'desc')
                           ->get();
        return view('pages.tables', compact('dokumens'));
    }
}
