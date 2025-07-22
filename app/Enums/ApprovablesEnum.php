<?php

namespace App\Enums;

use App\Models\Batch;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\Section;
use App\Models\Strategy;
use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum ApprovablesEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case Batch = 'batch';
    case Index = 'index';
    case Page = 'page';
    case Season = 'season';
    case Section = 'section';
    case Scheme = 'scheme';
    case Strategy = 'strategy';

    public static function fromClass(string $className): ?self
    {
        return match ($className) {
            Batch::class => self::Batch,
            Index::class => self::Index,
            Page::class => self::Page,
            Season::class => self::Season,
            Section::class => self::Section,
            Scheme::class => self::Scheme,
            Strategy::class => self::Strategy,
            default => null,
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::Batch => 'admin.batches',
            self::Index => 'admin.indices',
            self::Page => 'admin.pages',
            self::Season => 'admin.seasons',
            self::Section => 'admin.sections',
            self::Scheme => 'admin.schemes',
            self::Strategy => 'admin.strategies',
        };
    }

    public function viewComponent(): ?string
    {
        return match ($this) {
            self::Index => 'indexView',
            self::Section => 'sectionView',
            self::Page => 'pageView',
            default => null,
        };
    }
}
