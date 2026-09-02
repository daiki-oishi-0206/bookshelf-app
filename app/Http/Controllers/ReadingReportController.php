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
        $reviews->load('book.genres');

        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $reviews->unique('book_id')->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],

            'rating_distribution' => $reviews
                ->groupBy('rating')
                ->map->count()
                ->sortKeys(),

            'top_rated_books' => $reviews
                ->where('rating', '>=', 4)
                ->sortByDesc('rating')
                ->take(5)
                ->map(function ($review) {
                    return [
                        'id' => $review->book->id,
                        'title' => $review->book->title,
                        'author' => $review->book->author,
                        'rating' => $review->rating,
                    ];
                }),

            'genre_ratings' => $reviews
                ->flatMap(function ($review) {
                    return $review->book->genres->map(function ($genre) use ($review) {
                        return [
                            'id' => $genre->id,
                            'name' => $genre->name,
                            'rating' => $review->rating,
                        ];
                    });
                })
                ->groupBy('id')
                ->map(function ($genreReviews){
                    return[
                        'id' => $genreReviews->first()['id'],
                        'name' => $genreReviews->first()['name'],
                        'count' => $genreReviews->count(),
                        'average_rating' => $genreReviews->avg('rating'),
                    ];
                })
                ->sortByDesc('average_rating')
                ->take(5)
                ->values()
        ];

        return view('reports.index', compact('stats'));
    }
}


