<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Dependencies;
use Mrself\Options\DependencyContainerInterface;

trait DependencyContainerTrait
{
    protected function getDependencyContainer()
    {
        $container = new class implements DependencyContainerInterface {
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
        Dependencies::addContainer($container, 'app');
        return $container;
    }
}