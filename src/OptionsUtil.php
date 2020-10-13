<?php declare(strict_types=1);

namespace Mrself\Options;

class OptionsUtil
{
    public static function isClassOptionable(string $class): bool
    {
        $interfaces = class_implements($class);
        return in_array(OptionableInterface::class, $interfaces);
    }
}