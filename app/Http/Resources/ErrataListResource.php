<?php

namespace App\Http\Resources;

use App\Models\Errata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Errata */
class ErrataListResource extends JsonResource
{
    public function __construct(Errata $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');

        return [
            'id' => $this->id,
            'display_name' => sprintf('%s %s', $this->title, ! $this->published_at ? ' (Unpublished)' : ' (Published)'),
            'title' => $this->title,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'approved_at' => $this->approval?->approved_at,
            'published_at' => $this->published_at,
        ];
    }
}
