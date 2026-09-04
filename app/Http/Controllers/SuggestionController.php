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
use App\Models\Suggestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuggestionController extends Controller
{
    private const SUGGESTABLE_TYPES = [
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(self::SUGGESTABLE_TYPES)],
            'id' => ['required', 'integer'],
            'body' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        /** @var class-string<Model> $modelClass */
        $modelClass = $validated['type'];
        $model = $modelClass::withTrashed()->findOrFail($validated['id']);

        Suggestion::create([
            'user_id' => $request->user()->id,
            'suggestable_type' => $modelClass,
            'suggestable_id' => $model->stableContentId(),
            'body' => $validated['body'],
        ]);

        return response()->json(['message' => 'Thanks — your suggestion has been sent to the editors.']);
    }
}
