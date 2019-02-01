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

    /**
     * @param $container
     * @param string $key
     * @throws InvalidContainerException
     */
    public static function addContainer($container, string $key)
    {
        if (!static::isContainerValid($container)) {
            throw new InvalidContainerException($container);
        }
        static::$containers[$key] = $container;
    }

    public static function clearContainers()
    {
        static::$containers = [];
    }

    protected static function isContainerValid($container)
    {
        $methods = ['has', 'get', 'getParameter'];
        foreach ($methods as $method) {
            if (!method_exists($container, $method)) {
                return false;
            }
        }
        return true;
    }
}