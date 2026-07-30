<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function index(): View
        {
            return View('favorite.index');
        }

    public function toggle(Book $book): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);

            return back()->with('success', 'お気に入りから削除しました。');
        }

        $user->favoriteBooks()->attach($book->id);

        return back()->with('success', 'お気に入りに追加しました。');
    }
}
