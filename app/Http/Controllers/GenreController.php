<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function index(): View
    {
        /** @var Collection<int, Genre> $genres */
        $genres = Genre::all();

        return View('genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('books', 'genre'));
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()
                ->route('genres.index')
                ->with('error', 'このジャンルには書籍が紐付いているため削除できません');
        }

        $genre->delete();

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }

    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを作成しました');
    }

    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを更新しました');
    }
}
