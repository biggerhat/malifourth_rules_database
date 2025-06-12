<?php

namespace App\Traits;

use JetBrains\PhpStorm\ArrayShape;

trait UsesEnumSelectOptions
{
    #[ArrayShape(['name' => 'string', 'value' => 'string'])]
    public static function toSelectOptions(): array
    {
        return collect(self::cases())->transform(fn ($enum) => ['name' => $enum->label(), 'value' => $enum->value])->toArray();
    }
}
