<?php

namespace App\Http\Resources;

use App\Enums\ApprovablesEnum;
use App\Enums\PermissionEnum;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Approval */
class ApprovalResource extends JsonResource
{
    public function __construct(Approval $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing('approvable');
        $approvable = ApprovablesEnum::fromClass(get_class($this->approvable));
        $permissionName = PermissionEnum::modelPermissionName(get_class($this->approvable));
        $title = $this->approvable->title ?? $this->approvable->name ?? '';
        $type = $approvable->label();

        return [
            'id' => $this->id,
            'title' => $title,
            'type' => $type,
            'model' => $permissionName,
            'internal_notes' => $this->internal_notes,
            'approved_at' => $this->approved_at,
            /** @phpstan-ignore-next-line method.notFound */
            'is_approvable' => $this->approvable->canBeApproved(),
            /** @phpstan-ignore-next-line property.notFound */
            'published_at' => $this->approvable->published_at,
            'route_prefix' => $approvable->routePrefix(),
            /** @phpstan-ignore-next-line property.notFound */
            'route_binding' => $this->approvable->slug,
        ];
    }
}
