<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class EnumLabel
{
    public function __construct(public string $label) {}
}
