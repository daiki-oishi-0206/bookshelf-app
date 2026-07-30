<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateBookRequest;


class BookController extends Controller
{
    public function index(): View
    {
        $books = Book::with('genres')
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create(): View
    {
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'user_id' => Auth::id(),
        ]);

        $book->genres()->sync($request->genres);

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }


    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}