<?php

namespace App\Enums;

use App\Models\Batch;
use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
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
    case SeasonPage = 'season_page';
    case Faq = 'faq';
    case Errata = 'errata';

    public static function fromClass(string $className): ?self
    {
        return match ($className) {
            Batch::class => self::Batch,
            Errata::class => self::Errata,
            Faq::class => self::Faq,
            Index::class => self::Index,
            Page::class => self::Page,
            Season::class => self::Season,
            Section::class => self::Section,
            Scheme::class => self::Scheme,
            SeasonPage::class => self::SeasonPage,
            Strategy::class => self::Strategy,
            default => null,
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::Batch => 'admin.batches',
            self::Faq => 'admin.faqs',
            self::Index => 'admin.indices',
            self::Page => 'admin.pages',
            self::Season => 'admin.seasons',
            self::Section => 'admin.sections',
            self::Scheme => 'admin.schemes',
            self::SeasonPage => 'admin.season-pages',
            self::Strategy => 'admin.strategies',
            self::Errata => 'admin.errata',
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
