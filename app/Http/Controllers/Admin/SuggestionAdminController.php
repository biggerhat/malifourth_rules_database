<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\SeasonPage;
use App\Models\Suggestion;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $suggestions = Suggestion::with(['user', 'reviewedBy'])
            ->orderByRaw("status = 'pending' desc")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Suggestion $suggestion) {
                $modelClass = $suggestion->suggestable_type;
                $originalId = $suggestion->suggestable_id;

                $current = $modelClass::query()
                    ->where(function ($q) use ($originalId) {
                        $q->where('id', $originalId)->orWhere('original', $originalId);
                    })
                    ->published()
                    ->whereNull('newest')
                    ->first();

                if ($current instanceof SeasonPage) {
                    $current->loadMissing('season');
                }

                return [
                    'id' => $suggestion->id,
                    'body' => $suggestion->body,
                    'status' => $suggestion->status,
                    'submitted_by' => $suggestion->user?->name,
                    'submitted_at' => $suggestion->created_at->format('m-d-Y H:i'),
                    'reviewed_by' => $suggestion->reviewedBy?->name,
                    'reviewed_at' => $suggestion->reviewed_at?->format('m-d-Y H:i'),
                    'content' => $current ? [
                        'type' => class_basename($modelClass),
                        'title' => $current instanceof Faq
                            ? ContentBuilder::toPlainText($current->title)
                            : ContentBuilder::parseTitleTags($current->title),
                        'url' => ContentReferencesService::getRevisionUrl($current, $current),
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Suggestions/Index', [
            'suggestions' => $suggestions,
        ]);
    }

    public function markReviewed(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $suggestion->markReviewed($request->user());

        return redirect()->back()->withMessage('Suggestion marked as reviewed.');
    }

    public function dismiss(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $suggestion->dismiss($request->user());

        return redirect()->back()->withMessage('Suggestion dismissed.');
    }
}
