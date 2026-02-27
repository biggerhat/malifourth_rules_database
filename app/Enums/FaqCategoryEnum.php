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
            self::SpecificAbilities => 5,
            self::Arcanists => 6,
            self::Bayou => 7,
            self::ExplorersSociety => 8,
            self::Guild => 9,
            self::Neverborn => 10,
            self::Outcasts => 11,
            self::Resurrectionists => 12,
            self::TenThunders => 13,
        };
    }
}
