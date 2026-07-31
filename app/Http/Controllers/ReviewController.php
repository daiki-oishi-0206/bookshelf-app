<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        Review::create([
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'レビューを投稿しました');
    }

    public function like(Review $review)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);

            return back()->with('success', 'いいねを解除しました');
        }

        $user->likedReviews()->attach($review->id);

        return back()->with('success', 'いいねしました');
    }

    public function edit(Review $review): View
    {
        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'レビューを削除しました');
    }
}
