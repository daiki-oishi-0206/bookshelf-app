<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\Api\V1\BookDetailResource;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use GuzzleHttp\Psr7\Response;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {

            $query->where(function ($q) use ($request) {

                $q->where('title', 'like', "%{$request->keyword}%")
                    ->orWhere('author', 'like', "%{$request->keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre_id);
            });
        }

        $perPage = $request->input('per_page', 10);

        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        $genres = $data['genres'];
        unset($data['genres']);

        $data['user_id'] = 1;
        //Sanctum実装後はAuth::id()へ変更
        $book = Book::create($data);

        $book->genres()->attach($genres);

        return response('', 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        $book->reviews->loadCount('likedByUsers');

        return new BookDetailResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();
        $book->update($data);

        $book->genres()->sync($request->genres);

        return response('', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response('', 200);
    }
}
