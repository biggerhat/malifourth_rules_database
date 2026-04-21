<?php

namespace App\Http\Resources\API\V1;

use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Strategy */
class StrategyResource extends JsonResource
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
            'suit' => $this->suit->value,
            'suit_label' => $this->suit->label(),
            'setup' => $this->setup,
            'rules' => $this->rules,
            'scoring' => $this->scoring,
            'additional' => $this->additional,
            'front_image' => $this->front_image,
            'back_image' => $this->back_image,
            'combination_image' => $this->combination_image,
            'season_slug' => $this->whenLoaded('season', fn () => $this->season?->slug),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
