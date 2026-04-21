<?php

namespace App\Http\Resources\API\V1;

use App\Models\SeasonPage;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SeasonPage */
class SeasonPageResource extends JsonResource
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
            'sort_order' => $this->sort_order,
            'content' => $this->content,
            'content_text' => ContentBuilder::toSearchable($this->content ?? ''),
            'season_slug' => $this->whenLoaded('season', fn () => $this->season?->slug),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
