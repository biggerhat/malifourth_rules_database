<?php

namespace App\Http\Resources\API\V1;

use App\Models\Scheme;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Scheme */
class SchemeResource extends JsonResource
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
            'prerequisites' => $this->prerequisites,
            'reveal' => $this->reveal,
            'scoring' => $this->scoring,
            'additional' => $this->additional,
            'front_image' => $this->front_image,
            'back_image' => $this->back_image,
            'combination_image' => $this->combination_image,
            'next_scheme_slugs' => [
                $this->whenLoaded('nextScheme1', fn () => $this->nextScheme1?->slug),
                $this->whenLoaded('nextScheme2', fn () => $this->nextScheme2?->slug),
                $this->whenLoaded('nextScheme3', fn () => $this->nextScheme3?->slug),
            ],
            'season_slug' => $this->whenLoaded('season', fn () => $this->season?->slug),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
