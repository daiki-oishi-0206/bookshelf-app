<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReadingPlanController extends Controller
{
    public function index(Request $request): View
    {
        $currentStatus = $request->status;

        $readingPlans = auth()->user()
            ->readingPlans()
            ->when($currentStatus, function ($query) use ($currentStatus) {
                $query->where('status', $currentStatus);
            })
            ->get();

        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
    }

    public function create(): View
    {
        $books = Book::all();
        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request,): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $request->book_id,
            'status' => ReadingPlanStatus::NotStarted,
            'target_date' => $request->target_date,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を作成しました');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->target_date,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }

    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }

    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
        ]);

        return redirect()
            ->route('reading-plans.index');
    }
}


// ReadingPlanControllerの修正から