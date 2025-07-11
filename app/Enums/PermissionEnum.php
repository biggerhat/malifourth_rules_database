<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum PermissionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case ViewUser = 'view_user';
    case AddUser = 'add_user';
    case EditUser = 'edit_user';
    case DeleteUser = 'delete_user';

    case ViewRole = 'view_role';
    case AddRole = 'add_role';
    case EditRole = 'edit_role';
    case DeleteRole = 'delete_role';

    case ViewBatch = 'view_batch';
    case AddBatch = 'add_batch';
    case EditBatch = 'edit_batch';
    case DeleteBatch = 'delete_batch';
    case ApproveBatch = 'approve_batch';
    case PublishBatch = 'publish_batch';

    case ViewIndex = 'view_index';
    case AddIndex = 'add_index';
    case EditIndex = 'edit_index';
    case DeleteIndex = 'delete_index';
    case ApproveIndex = 'approve_index';
    case PublishIndex = 'publish_index';

    case ViewSection = 'view_section';
    case AddSection = 'add_section';
    case EditSection = 'edit_section';
    case DeleteSection = 'delete_section';
    case ApproveSection = 'approve_section';
    case PublishSection = 'publish_section';

    case ViewPage = 'view_page';
    case AddPage = 'add_page';
    case EditPage = 'edit_page';
    case DeletePage = 'delete_page';
    case ApprovePage = 'approve_page';
    case PublishPage = 'publish_page';

    case ViewSeason = 'view_season';
    case AddSeason = 'add_season';
    case EditSeason = 'edit_season';
    case DeleteSeason = 'delete_season';
    case ApproveSeason = 'approve_season';
    case PublishSeason = 'publish_season';

    case ViewScheme = 'view_scheme';
    case AddScheme = 'add_scheme';
    case EditScheme = 'edit_scheme';
    case DeleteScheme = 'delete_scheme';
    case ApproveScheme = 'approve_scheme';
    case PublishScheme = 'publish_scheme';

    case ViewStrategy = 'view_strategy';
    case AddStrategy = 'add_strategy';
    case EditStrategy = 'edit_strategy';
    case DeleteStrategy = 'delete_strategy';
    case ApproveStrategy = 'approve_strategy';
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
        return match($class) {
            default => \Str::snake($modelName),
        };
    }
}
