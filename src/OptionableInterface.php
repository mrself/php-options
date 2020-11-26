<?php declare(strict_types=1);

namespace Mrself\Options;

interface OptionableInterface
{
    /**
     * @param array $options
     * @return static
     */
    public static function make(array $options = []);
}