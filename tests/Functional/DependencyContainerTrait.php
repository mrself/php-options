<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Container\ContainerInterface;
use Mrself\Container\Registry\ContainerRegistry;

trait DependencyContainerTrait
{
    protected function getDependencyContainer(): ContainerInterface
    {
        return ContainerRegistry::get('Mrself\\Options');
    }
}