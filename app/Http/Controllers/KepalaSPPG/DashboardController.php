<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('KepalaSPPG.dashboard');
    }

    public function auditIndex(): RedirectResponse
    {
        return redirect()->route('sppg.dashboard');
    }
}
