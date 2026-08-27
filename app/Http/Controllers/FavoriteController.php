<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\User;

class FavoriteController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);

            return back()->with('success', 'お気に入りから削除しました');
        }

        $user->favoriteBooks()->attach($book->id);

        return back()->with('success', 'お気に入りに追加しました');
    }



}
