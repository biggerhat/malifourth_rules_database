<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Errata;
use App\Services\WyrdNews\WyrdNews;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $batches = Batch::where('is_public', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(10)
            ->get()
            ->map(fn (Batch $batch) => [
                'title' => $batch->title,
                'href' => route('errata.batch', $batch->slug),
                'published_at' => $batch->published_at,
                'type' => 'Rules Errata',
            ]);

        $errata = Errata::whereNotNull('published_at')
            ->whereNull('newest')
            ->orderByDesc('published_at')
            ->limit(10)
            ->get()
            ->map(fn (Errata $e) => [
                'title' => $e->title,
                'href' => route('errata.view', $e->slug),
                'published_at' => $e->published_at,
                'type' => 'Errata',
            ]);

        $latestUpdates = $batches->concat($errata)
            ->sortByDesc('published_at')
            ->take(10)
            ->map(fn ($item) => [
                'title' => $item['title'],
                'href' => $item['href'],
                'published_at' => $item['published_at']->format('m-d-Y'),
                'type' => $item['type'],
            ])
            ->values();

        return Inertia::render('Index', [
            'wyrd_news' => WyrdNews::fetchLatest(),
            'latest_updates' => $latestUpdates,
        ]);
    }
}
