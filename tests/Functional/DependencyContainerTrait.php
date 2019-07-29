<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Container\Container;
use Mrself\Container\ContainerInterface;
use Mrself\Container\Registry\ContainerRegistry;

trait DependencyContainerTrait
{
    protected function getDependencyContainer(): ContainerInterface
    {
        $container = Container::make();
        ContainerRegistry::add('Mrself\\Options', $container);
        return $container;
    }
}