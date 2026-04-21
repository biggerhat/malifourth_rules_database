<?php

namespace App\Http\Resources\API\V1;

use App\Models\Page;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Page */
class PageResource extends JsonResource
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
            'page_number' => $this->page_number,
            'book_page_numbers' => $this->book_page_numbers,
            'left_column' => $this->left_column,
            'right_column' => $this->right_column,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
