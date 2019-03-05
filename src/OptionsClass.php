<?php declare(strict_types=1);

namespace Mrself\Options;

use PhpDocReader\PhpDocReader;

class OptionsClass
{
    private static $cache = [];

    public static function define($object)
    {
        $class = get_class($object);
        if (array_key_exists($class, static::$cache)) {
            return static::$cache[$class];
        }
        $docReader = new PhpDocReader();
        $reflectionProperty = new \ReflectionProperty($object, 'options');
        $optionClass = $docReader->getPropertyClass($reflectionProperty);
        static::$cache[$class] = $optionClass;
        return $optionClass;
    }

    public static function clearCache()
    {
        static::$cache = [];
    }
}