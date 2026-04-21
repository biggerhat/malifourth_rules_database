<?php

namespace App\Http\Resources\API\V1;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Batch */
class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'release_notes' => $this->release_notes,
            'published_at' => $this->published_at?->toIso8601String(),
            'contents' => [
                'errata' => ErrataResource::collection($this->whenLoaded('errata')),
                'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
                'indices' => IndexResource::collection($this->whenLoaded('indices')),
                'pages' => PageResource::collection($this->whenLoaded('pages')),
                'sections' => SectionResource::collection($this->whenLoaded('sections')),
                'schemes' => SchemeResource::collection($this->whenLoaded('schemes')),
                'seasons' => SeasonResource::collection($this->whenLoaded('seasons')),
                'season_pages' => SeasonPageResource::collection($this->whenLoaded('seasonPages')),
                'strategies' => StrategyResource::collection($this->whenLoaded('strategies')),
            ],
        ];
    }
}
