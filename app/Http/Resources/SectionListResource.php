<?php

namespace App\Http\Resources;

use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Section */
class SectionListResource extends JsonResource
{
    public function __construct(Section $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');

        return [
            'id' => $this->id,
            'display_name' => sprintf('%s %s', ContentBuilder::parseTitleTags($this->title), ! $this->published_at ? ' (Unpublished)' : ' (Published)'),
            'title' => $this->title,
            'slug' => $this->slug,
            'approved_at' => $this->approval?->approved_at,
            'published_at' => $this->published_at,
        ];
    }
}
