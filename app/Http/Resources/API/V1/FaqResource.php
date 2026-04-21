<?php

namespace App\Http\Resources\API\V1;

use App\Models\Faq;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Faq */
class FaqResource extends JsonResource
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
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'sort_order' => $this->sort_order,
            'answer' => $this->answer,
            'answer_text' => ContentBuilder::toSearchable($this->answer ?? ''),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
