<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Container\ContainerInterface;
use Mrself\Container\Registry\ContainerRegistry;

trait DependencyContainerTrait
{
    protected function getDependencyContainer()
    {
        $container = new class implements ContainerInterface {
            public $services = [];
            public $parameters = [];

            public function get(string $name)
            {
                return $this->services[$name];
            }

            public function has(string $name)
            {
                return true;
            }

            public function getParameter(string $name)
            {
                return $this->parameters[$name];
            }
        };
        ContainerRegistry::add('Mrself\\Options', $container);
        return $container;
    }
}