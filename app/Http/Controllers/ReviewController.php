<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        Review::create([
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'レビューを投稿しました');
    }

    public function like(Review $review): RedirectResponse
    {
        /** @var User $user */
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
        $this->authorize('update', $review);
        
        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        
        $review->delete();

        return redirect()
            ->route('books.show', $review->book)
            ->with('success', 'レビューを削除しました');
    }
}
