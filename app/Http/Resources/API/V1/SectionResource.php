<?php

namespace App\Http\Resources\API\V1;

use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Section */
class SectionResource extends JsonResource
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
            'left_column' => $this->left_column,
            'right_column' => $this->right_column,
            'content_text' => ContentBuilder::toSearchable(($this->left_column ?? '').' '.($this->right_column ?? '')),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
