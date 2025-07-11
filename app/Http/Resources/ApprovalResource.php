<?php

namespace App\Http\Resources;

use App\Enums\ApprovablesEnum;
use App\Enums\PermissionEnum;
use App\Models\Approval;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

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
            'approved_at' => $this->approved_at,
            'published_at' => $this->approvable->published_at,
            'route_prefix' => $approvable->routePrefix(),
            'route_binding' => $this->approvable->slug,
        ];
    }
}
