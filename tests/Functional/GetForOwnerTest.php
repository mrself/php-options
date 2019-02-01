<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Annotation\Option;
use Mrself\Options\Options;
use Mrself\Options\WithOptionsTrait;
use PHPUnit\Framework\TestCase;

class GetForOwnerTest extends TestCase
{
    use DependencyContainerTrait;

    public function testItOmitsOptionIfAnnotationIsConfigured()
    {
        $container = $this->getDependencyContainer();
        $container->services['Reflection'] = new \Reflection();
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \Reflection
             */
            public $option1;
        };
        $object->init();
        $this->assertEquals($container->services['Reflection'], $object->option1);
    }
}