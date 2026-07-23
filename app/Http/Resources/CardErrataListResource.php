<?php

namespace App\Http\Resources;

use App\Models\CardErrata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CardErrata */
class CardErrataListResource extends JsonResource
{
    public function __construct(CardErrata $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');

        return [
            'id' => $this->id,
            'display_name' => sprintf('%s %s', $this->card_name, ! $this->published_at ? ' (Unpublished)' : ' (Published)'),
            'faction' => $this->faction,
            'card_name' => $this->card_name,
            'slug' => $this->slug,
            'approved_at' => $this->approval?->approved_at,
            'published_at' => $this->published_at,
        ];
    }
}
