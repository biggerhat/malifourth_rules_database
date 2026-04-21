<?php

namespace App\Http\Resources\API\V1;

use App\Models\Season;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Season */
class SeasonResource extends JsonResource
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
            'title_text' => ContentBuilder::toPlainText($this->title),
            'content' => $this->content,
            'content_text' => ContentBuilder::toSearchable($this->content ?? ''),
            'url' => $this->url,
            'strategies' => StrategyResource::collection($this->whenLoaded('strategies')),
            'schemes' => SchemeResource::collection($this->whenLoaded('schemes')),
            'season_pages' => SeasonPageResource::collection($this->whenLoaded('seasonPages')),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
