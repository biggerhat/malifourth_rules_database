<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;

class EntitySearchController extends Controller
{
    public function search(Request $request): array
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'index');

        if (strlen($query) < 2) {
            return [];
        }

        $model = match ($type) {
            'index' => Index::class,
            'section' => Section::class,
            'page' => Page::class,
            'faq' => Faq::class,
            default => Index::class,
        };

        return $model::where('title', 'LIKE', "%{$query}%")
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'display_name' => $item->display_name ?? $item->title,
                'type' => $type,
            ])
            ->values()
            ->all();
    }
}
