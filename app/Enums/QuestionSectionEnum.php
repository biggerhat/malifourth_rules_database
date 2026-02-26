<?php

namespace App\Enums;

use App\Attributes\EnumLabel;
use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum QuestionSectionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case General = 'general';
    case Actions = 'actions';
    case Terrain = 'terrain';
    case Encounters = 'encounters';
    #[EnumLabel('Specific Abilities, Actions, and Triggers')]
    case Specifics = 'specifics';

    case Arcanists = 'arcanists';
    case Bayou = 'bayou';
    #[EnumLabel('Explorer\'s Society')]
    case ExplorersSociety = 'explorers_society';
    case Guild = 'guild';
    case Neverborn = 'neverborn';
    case Outcasts = 'outcasts';
    case Resurrectionists = 'resurrectionists';
    case TenThunders = 'ten_thunders';
}
