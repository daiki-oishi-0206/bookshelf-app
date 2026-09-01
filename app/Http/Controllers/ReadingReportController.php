<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingReportController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $reviews = $user->reviews;

        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $reviews->unique('book_id')->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],
        ];

        return view('reports.index', compact('stats'));
    }
}
