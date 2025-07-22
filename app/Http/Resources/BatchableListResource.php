<?php

namespace App\Http\Resources;

use App\Enums\ApprovablesEnum;
use App\Enums\PermissionEnum;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Model */
class BatchableListResource extends JsonResource
{
    public function __construct(Model $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approval');
        $approvable = ApprovablesEnum::fromClass(get_class($this->resource));
        $permissionName = PermissionEnum::modelPermissionName(get_class($this->resource));
        $title = $this->title ?? $this->name ?? '';
        $type = $approvable->label();

        return [
            /** @phpstan-ignore-next-line property.notFound */
            'id' => $this->id,
            'title' => $title,
            /** @phpstan-ignore-next-line property.notFound */
            'slug' => $this->slug,
            'type' => $type,
            'component' => $approvable->viewComponent(),
            'model' => $permissionName,
            'content' => (new ContentBuilder($this->content ?? ''))->getFullyHydratedContent(),
            /** @phpstan-ignore-next-line property.notFound */
            'internal_notes' => $this->internal_notes,
            /** @phpstan-ignore-next-line property.notFound */
            'approval_id' => $this->approval?->id,
            /** @phpstan-ignore-next-line property.notFound */
            'approved_at' => $this->approval?->approved_at,
            /** @phpstan-ignore-next-line method.notFound */
            'is_approvable' => $this->canBeApproved(),
            /** @phpstan-ignore-next-line property.notFound */
            'published_at' => $this->published_at,
            'route_prefix' => $approvable->routePrefix(),
            /** @phpstan-ignore-next-line property.notFound */
            'route_binding' => $this->slug,
        ];
    }
}
