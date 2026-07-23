<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function view(Request $request, Section $section)
    {
        $section->loadMissing('newestVersion', 'publishedBy');
        $section = $section->newestVersion ?? $section;

        if (! $section->published_at) {
            return response('', 404);
        }

        return $this->renderSection($section);
    }

    public function viewHistory(Request $request, Section $section)
    {
        $section->loadMissing('newestVersion', 'publishedBy');

        $currentVersion = $section->newestVersion ?? $section;

        if ($currentVersion->id === $section->id) {
            return redirect()->route('rules.section.view', $section->slug);
        }

        if (! $section->published_at) {
            return response('', 404);
        }

        return $this->renderSection($section, [
            'viewing_old_version' => true,
            'current_version_url' => route('rules.section.view', $currentVersion->slug),
        ]);
    }

    private function renderSection(Section $section, array $extra = [])
    {
        $leftColumn = (new ContentBuilder($section->left_column ?? ''))->getFullyHydratedContent();
        $rightColumn = (new ContentBuilder($section->right_column ?? ''))->getFullyHydratedContent();

        $description = Str::limit(
            ContentBuilder::toSearchable(($section->left_column ?? '').' '.($section->right_column ?? '')),
            155
        );

        return inertia('Rules/SectionView', array_merge([
            'title' => ContentBuilder::parseTitleTags($section->title),
            'title_text' => ContentBuilder::toPlainText($section->title),
            'meta_description' => $description,
            'left_column' => $leftColumn,
            'right_column' => $rightColumn,
            'published_at' => $section->published_at->format('m-d-Y'),
            'published_by' => $section->publishedBy->name,
            'references' => ContentReferencesService::getForModel($section),
        ], $extra));
    }
}
