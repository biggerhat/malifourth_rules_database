<?php

namespace App\Http\Resources;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Batch */
class BatchListResource extends JsonResource
{
    public function __construct(Batch $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_approvable' => $this->canBeApproved(),
            'approval' => $this->approval ?? null,
            'approved_at' => $this->approval?->approved_at,
            'published_at' => $this->published_at,
        ];
    }
}
