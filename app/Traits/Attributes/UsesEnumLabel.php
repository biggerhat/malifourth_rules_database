<?php

namespace App\Traits\Attributes;

use App\Attributes\EnumLabel;
use Illuminate\Support\Str;

trait UsesEnumLabel
{
    public function label(): string
    {
        $attributes = (new \ReflectionClassConstant(
            class: self::class,
            constant: $this->name,
        ))->getAttributes(
            name: EnumLabel::class,
        );

        if (! count($attributes)) {
            return Str::headline($this->name);
        }

        return $attributes[0]->newInstance()->label;
    }
}
