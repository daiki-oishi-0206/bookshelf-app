<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

class RankingController extends Controller
{
    public function index(): View
    {
        /** @var Collection<int, Book> $rankedBooks */
        $rankedBooks = Book::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->has('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderBy('id')
            ->take(10)
            ->get();

        return View('ranking.index', compact('rankedBooks'));
    }
}
