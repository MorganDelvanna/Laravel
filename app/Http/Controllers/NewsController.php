<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewsController extends Controller
{
    public function list(): JsonResponse
    {
        try {
            $newsItems = News::query()
            ->where('archived', false)
            ->orderByDesc('publish_date')
            ->get();

            return response()->json($newsItems);
        } catch (Throwable $e) {
            Log::error('Error fetching news items: ' . $e->getMessage());
            // Return an empty JSON array with an HTTP 200 (or 500) status
            return response()->json(['header' => 'Unable to fetch news items'], 500);
        }

    }

    public function newsList()
    {
        $newsItems = News::query()
            ->orderByDesc('publish_date')
            ->get();

        return view('admin.news', compact('newsItems'));
    }

    public function saveNews(Request $request)
    {
        // TODO: update the description field with a rich text editor in the future
        $validated = $request->validate([
            'newsID' => ['nullable', 'integer', 'exists:news,id'],
            'publish_date' => ['required', 'date_format:Y-m-d'],
            'header' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'archived' => ['sometimes', 'boolean'],
        ]);

        $newsItem = !empty($validated['newsID'])
            ? News::findOrFail($validated['newsID'])
            : new News();

        $newsItem->fill([
            'publish_date' => $validated['publish_date'],
            'header' => $validated['header'],
            'description' => $validated['description'] ?? "",
            'archived' => $validated['archived'] ?? false,
        ]);
        $newsItem->save();

        return redirect()
            ->route('news.admin')
            ->with('status', $validated['newsID'] ? 'News item updated.' : 'News item created.');
    }

    public function deleteNews($id)
    {
        $newsItem = News::findOrFail($id);
        $newsItem->delete();

        return redirect()
            ->route('news.admin')
            ->with('status', 'News item deleted.');
    }

    public function archiveNews($id)
    {
        $newsItem = News::findOrFail($id);
        $newsItem->archived = !$newsItem->archived;
        $newsItem->save();

        return redirect()
            ->route('news.admin')
            ->with('status', 'News item archived.');
    }
}
