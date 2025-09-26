<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // jumlah user bulan ini
        $currentMonth = User::whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        // jumlah user bulan lalu
        $lastMonth = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
                         ->whereYear('created_at', Carbon::now()->subMonth()->year)
                         ->count();

        // hitung persentase perubahan
        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        }

        // total user keseluruhan
        $totalUsers = User::count();

        return view('dashboard.index', compact('totalUsers','growth'));
    }
}
