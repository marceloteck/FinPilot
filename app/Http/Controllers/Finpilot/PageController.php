<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home()
    {
        return Inertia::render('Pages/Home');
    }

    public function transactions()
    {
        return Inertia::render('Pages/Transactions');
    }

    public function debts()
    {
        return Inertia::render('Pages/Debts');
    }

    public function ai()
    {
        return Inertia::render('Pages/AI');
    }

    public function goals()
    {
        return Inertia::render('Pages/Goals');
    }

    public function reports()
    {
        return Inertia::render('Pages/Reports');
    }

    public function settings()
    {
        return Inertia::render('Pages/Settings');
    }
}
