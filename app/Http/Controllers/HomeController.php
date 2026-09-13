<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the application landing homepage.
     */
    public function index(): View
    {
        $recentBooks = Ebook::where('status', 'published')
            ->latest()
            ->take(6)
            ->get();

        $totalBooks = Ebook::count();
        $totalPages = Ebook::sum('total_pages') ?: 0;

        return view('home', compact('recentBooks', 'totalBooks', 'totalPages'));
    }
}
