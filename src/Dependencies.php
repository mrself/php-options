<?php declare(strict_types=1);

namespace Mrself\Options;

class Dependencies
{
    /**
     * @var DependencyContainerInterface[]
     */
    protected static $containers = [];

    public static function get(string $containerKey, string $dependencyName)
    {
        return static::$containers[$containerKey]->get($dependencyName);
    }

    public static function getParameter(string $containerKey, string $dependencyName)
    {
        return static::$containers[$containerKey]->getParameter($dependencyName);
    }

    public static function addContainer(DependencyContainerInterface $container, string $key)
    {
        static::$containers[$key] = $container;
    }

    public static function clearContainers()
    {
        static::$containers = [];
    }
}