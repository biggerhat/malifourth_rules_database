<?php

namespace App\Http\Controllers\Rules;

use App\Enums\FaqCategoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faqsByCategory = Faq::whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (Faq $faq) {
                return [
                    'id' => $faq->id,
                    'title' => (new ContentBuilder($faq->title))->getFullyHydratedContent(),
                    'slug' => $faq->slug,
                    'category' => $faq->category->value,
                    'category_label' => $faq->category->label(),
                    'category_sort' => $faq->category->sortOrder(),
                    'sort_order' => $faq->sort_order,
                    'answer' => (new ContentBuilder($faq->answer ?? ''))->getFullyHydratedContent(),
                ];
            })
            ->sortBy(['category_sort', 'sort_order'])
            ->groupBy('category');

        $allCategories = collect(FaqCategoryEnum::cases())
            ->sortBy(fn (FaqCategoryEnum $c) => $c->sortOrder())
            ->map(fn (FaqCategoryEnum $c) => [
                'key' => $c->value,
                'label' => $c->label(),
                'items' => ($faqsByCategory[$c->value] ?? collect())->values()->all(),
            ])
            ->values()
            ->all();

        return inertia('Rules/FaqView', [
            'categories' => $allCategories,
        ]);
    }

    public function view(Request $request, Faq $faq)
    {
        $faq->loadMissing('newestVersion', 'publishedBy');
        $faq = $faq->newestVersion ?? $faq;

        if (! $faq->published_at) {
            return response('', 404);
        }

        $answer = (new ContentBuilder($faq->answer ?? ''))->getFullyHydratedContent();

        return inertia('Rules/FaqView', [
            'faq' => $this->serializeFaq($faq, $answer),
            'references' => ContentReferencesService::getForModel($faq),
        ]);
    }

    public function viewHistory(Request $request, Faq $faq)
    {
        $faq->loadMissing('newestVersion', 'publishedBy');

        $currentVersion = $faq->newestVersion ?? $faq;

        if ($currentVersion->id === $faq->id) {
            return redirect()->route('rules.faq.view', $faq->slug);
        }

        if (! $faq->published_at) {
            return response('', 404);
        }

        $answer = (new ContentBuilder($faq->answer ?? ''))->getFullyHydratedContent();

        return inertia('Rules/FaqView', [
            'faq' => $this->serializeFaq($faq, $answer),
            'references' => ContentReferencesService::getForModel($faq),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.faq.view', $currentVersion->slug),
        ]);
    }

    private function serializeFaq(Faq $faq, array $answer): array
    {
        return [
            'title' => (new ContentBuilder($faq->title))->getFullyHydratedContent(),
            'title_text' => ContentBuilder::toSearchable($faq->title),
            'slug' => $faq->slug,
            'category' => $faq->category->value,
            'category_label' => $faq->category->label(),
            'answer' => $answer,
            'published_at' => $faq->published_at->format('m-d-Y'),
            'published_by' => $faq->publishedBy->name,
        ];
    }
}
