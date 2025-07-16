<?php

namespace App\Http\Resources;

use App\Enums\ApprovablesEnum;
use App\Enums\PermissionEnum;
use App\Models\Approval;
use App\Models\Index;
use App\Models\Page;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin Page */
class PageListResource extends JsonResource
{
    public function __construct(Page $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');

        return [
            'id' => $this->id,
            'title' => sprintf("%s %s", $this->title, !$this->published_at ? ' (Unpublished)' : ' (Published)'),
            'slug' => $this->slug,
            'approved_at' => $this->approval?->approved_at,
            'published_at' => $this->published_at,
        ];
    }
}
