<?php

namespace App\Enums;

use App\Attributes\PermissionGroup;
use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;
use ReflectionEnum;

enum PermissionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    #[PermissionGroup(PermissionGroupEnum::User)]
    case ViewUser = 'view_user';
    #[PermissionGroup(PermissionGroupEnum::User)]
    case AddUser = 'add_user';
    #[PermissionGroup(PermissionGroupEnum::User)]
    case EditUser = 'edit_user';
    #[PermissionGroup(PermissionGroupEnum::User)]
    case DeleteUser = 'delete_user';

    #[PermissionGroup(PermissionGroupEnum::Role)]
    case ViewRole = 'view_role';
    #[PermissionGroup(PermissionGroupEnum::Role)]
    case AddRole = 'add_role';
    #[PermissionGroup(PermissionGroupEnum::Role)]
    case EditRole = 'edit_role';
    #[PermissionGroup(PermissionGroupEnum::Role)]
    case DeleteRole = 'delete_role';

    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case ViewBatch = 'view_batch';
    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case AddBatch = 'add_batch';
    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case EditBatch = 'edit_batch';
    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case DeleteBatch = 'delete_batch';
    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case ApproveBatch = 'approve_batch';
    #[PermissionGroup(PermissionGroupEnum::Batch)]
    case PublishBatch = 'publish_batch';

    #[PermissionGroup(PermissionGroupEnum::Index)]
    case ViewIndex = 'view_index';
    #[PermissionGroup(PermissionGroupEnum::Index)]
    case AddIndex = 'add_index';
    #[PermissionGroup(PermissionGroupEnum::Index)]
    case EditIndex = 'edit_index';
    #[PermissionGroup(PermissionGroupEnum::Index)]
    case DeleteIndex = 'delete_index';
    #[PermissionGroup(PermissionGroupEnum::Index)]
    case ApproveIndex = 'approve_index';
    #[PermissionGroup(PermissionGroupEnum::Index)]
    case PublishIndex = 'publish_index';

    #[PermissionGroup(PermissionGroupEnum::Section)]
    case ViewSection = 'view_section';
    #[PermissionGroup(PermissionGroupEnum::Section)]
    case AddSection = 'add_section';
    #[PermissionGroup(PermissionGroupEnum::Section)]
    case EditSection = 'edit_section';
    #[PermissionGroup(PermissionGroupEnum::Section)]
    case DeleteSection = 'delete_section';
    #[PermissionGroup(PermissionGroupEnum::Section)]
    case ApproveSection = 'approve_section';
    #[PermissionGroup(PermissionGroupEnum::Section)]
    case PublishSection = 'publish_section';

    #[PermissionGroup(PermissionGroupEnum::Page)]
    case ViewPage = 'view_page';
    #[PermissionGroup(PermissionGroupEnum::Page)]
    case AddPage = 'add_page';
    #[PermissionGroup(PermissionGroupEnum::Page)]
    case EditPage = 'edit_page';
    #[PermissionGroup(PermissionGroupEnum::Page)]
    case DeletePage = 'delete_page';
    #[PermissionGroup(PermissionGroupEnum::Page)]
    case ApprovePage = 'approve_page';
    #[PermissionGroup(PermissionGroupEnum::Page)]
    case PublishPage = 'publish_page';

    #[PermissionGroup(PermissionGroupEnum::Season)]
    case ViewSeason = 'view_season';
    #[PermissionGroup(PermissionGroupEnum::Season)]
    case AddSeason = 'add_season';
    #[PermissionGroup(PermissionGroupEnum::Season)]
    case EditSeason = 'edit_season';
    #[PermissionGroup(PermissionGroupEnum::Season)]
    case DeleteSeason = 'delete_season';
    #[PermissionGroup(PermissionGroupEnum::Season)]
    case ApproveSeason = 'approve_season';
    #[PermissionGroup(PermissionGroupEnum::Season)]
    case PublishSeason = 'publish_season';

    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case ViewScheme = 'view_scheme';
    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case AddScheme = 'add_scheme';
    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case EditScheme = 'edit_scheme';
    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case DeleteScheme = 'delete_scheme';
    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case ApproveScheme = 'approve_scheme';
    #[PermissionGroup(PermissionGroupEnum::Scheme)]
    case PublishScheme = 'publish_scheme';

    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case ViewStrategy = 'view_strategy';
    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case AddStrategy = 'add_strategy';
    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case EditStrategy = 'edit_strategy';
    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case DeleteStrategy = 'delete_strategy';
    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case ApproveStrategy = 'approve_strategy';
    #[PermissionGroup(PermissionGroupEnum::Strategy)]
    case PublishStrategy = 'publish_strategy';

    //    case View = 'view_';
    //    case Add = 'add_';
    //    case Edit = 'edit_';
    //    case Delete = 'delete_';
    //    case Approve = 'approve_';
    //    case Publish = 'publish_';

    public static function modelPermissionName(string $class): string
    {
        $modelName = explode('\\', $class);
        $modelName = end($modelName);

        return match ($class) {
            default => \Str::snake($modelName),
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getPermissionsByGroup(PermissionGroupEnum $targetGroup): array
    {
        $result = [];

        $reflection = new ReflectionEnum(self::class);

        foreach ($reflection->getCases() as $case) {
            foreach ($case->getAttributes(PermissionGroup::class) as $attribute) {
                /** @var PermissionGroup $permissionGroup */
                $permissionGroup = $attribute->newInstance();
                if ($permissionGroup->permissionGroup === $targetGroup) {
                    /** @var PermissionEnum $permission */
                    $permission = $case->getValue();
                    $result[] = ['name' => $permission->label(), 'value' => $permission->value];
                }
            }
        }

        return $result;
    }
}
