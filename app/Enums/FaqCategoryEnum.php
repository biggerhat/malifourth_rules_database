<?php

namespace App\Enums;

use App\Attributes\EnumLabel;
use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum FaqCategoryEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case General = 'general';
    case Actions = 'actions';
    case Terrain = 'terrain';
    case Encounters = 'encounters';
    case Campaign = 'campaign';
    #[EnumLabel('Specific Abilities, Actions, and Triggers')]
    case SpecificAbilities = 'specific_abilities';
    case Arcanists = 'arcanists';
    case Bayou = 'bayou';
    #[EnumLabel("Explorer's Society")]
    case ExplorersSociety = 'explorers_society';
    case Guild = 'guild';
    case Neverborn = 'neverborn';
    case Outcasts = 'outcasts';
    case Resurrectionists = 'resurrectionists';
    #[EnumLabel('Ten Thunders')]
    case TenThunders = 'ten_thunders';

    public function sortOrder(): int
    {
        return match ($this) {
            self::General => 1,
            self::Actions => 2,
            self::Terrain => 3,
            self::Encounters => 4,
            self::Campaign => 5,
            self::SpecificAbilities => 6,
            self::Arcanists => 7,
            self::Bayou => 8,
            self::ExplorersSociety => 9,
            self::Guild => 10,
            self::Neverborn => 11,
            self::Outcasts => 12,
            self::Resurrectionists => 13,
            self::TenThunders => 14,
        };
    }
}
