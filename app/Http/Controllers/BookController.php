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
use App\Http\Requests\IndexBookRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;



class BookController extends Controller
{
    public function index(IndexBookRequest $request): View
    {
        $query = Book::query()
        ->with('genres')
        ->withAvg('reviews', 'rating');

        if($request->filled('keyword')){
            $query->where(function($q)use($request){
                $q->where('title', 'like', "%{$request->keyword}%")
                ->orWhere('author', 'like', "%{$request->keyword}%");
            });
        }

        if($request->filled('genre')){
            $query->whereHas('genres', function($q) use ($request){
                $q->where('genres.id', $request->genre);
            });
        }
        
        if($request->sort === 'newest'){
            $query->orderByDesc('created_at');
        }elseif($request->sort === 'oldest'){
            $query->orderBy('created_at');
        }elseif($request->sort === 'rating'){
            $query->orderByDesc('reviews_avg_rating');
        }elseif($request->sort === 'title'){
            $query->orderBy('title');
        }


        $books = $query->paginate(10)->withQueryString();
        $genres = Genre::all();
            
        return view('books.index', compact('books', 'genres'));
    }

    public function create(): View
    {
        /** @var Collection<int, Genre> $genres */
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function isbnSearch(string $isbn)
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Google Books APIとの通信に失敗しました。',
            ], 500);
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'error' => '書籍情報が見つかりませんでした。',
            ], 404);
        }

        $book = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $book['title'] ?? '',
            'author' => $book['authors'][0] ?? '',
            'description' => $book['description'] ?? '',
            'image_url' => $book['imageLinks']['thumbnail'] ?? '',
            'published_date' => $book['publishedDate'] ?? '',
        ]);
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
            ->with('success', '書籍を登録しました');
    }


    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);
        /** @var Collection<int, Genre> $genres */
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}

