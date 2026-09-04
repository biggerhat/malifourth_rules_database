<?php

namespace App\Http\Controllers;

use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Section;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    private const FAVORITABLE_TYPES = [
        Page::class,
        Section::class,
        Index::class,
        Faq::class,
        Errata::class,
        Season::class,
        SeasonPage::class,
        Strategy::class,
        Scheme::class,
    ];

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(self::FAVORITABLE_TYPES)],
            'id' => ['required', 'integer'],
        ]);

        /** @var class-string<Model> $modelClass */
        $modelClass = $validated['type'];
        $model = $modelClass::withTrashed()->findOrFail($validated['id']);

        $favorited = $model->toggleFavorite($request->user());

        return response()->json(['favorited' => $favorited]);
    }

    public function index(Request $request): Response
    {
        $favorites = $request->user()
            ->favorites()
            ->latest()
            ->get()
            ->map(function ($favorite) {
                $modelClass = $favorite->favoritable_type;
                $originalId = $favorite->favoritable_id;

                $current = $modelClass::query()
                    ->where(function ($q) use ($originalId) {
                        $q->where('id', $originalId)->orWhere('original', $originalId);
                    })
                    ->published()
                    ->whereNull('newest')
                    ->first();

                if (! $current) {
                    return null;
                }

                if ($current instanceof SeasonPage) {
                    $current->loadMissing('season');
                }

                return [
                    'type' => class_basename($modelClass),
                    'title' => $current instanceof Faq
                        ? ContentBuilder::toPlainText($current->title)
                        : ContentBuilder::parseTitleTags($current->title),
                    'url' => ContentReferencesService::getRevisionUrl($current, $current),
                    'favorited_at' => $favorite->created_at->format('m-d-Y'),
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('Favorites/Index', [
            'favorites' => $favorites,
        ]);
    }
}
