<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

class GetForOwnerTest extends TestCase
{
    use DependencyContainerTrait;

    public function testItOmitsOptionIfAnnotationIsConfigured()
    {
        $container = $this->getDependencyContainer();
        $container->set('Reflection', new \Reflection());
        $object = new class {
            use WithOptionsTrait;

            protected $optionsContainerNamespace = 'Mrself\\Options';

            /**
             * @Option
             * @var \Reflection
             */
            public $option1;
        };
        $object->init();
        $this->assertEquals($container->get('Reflection'), $object->option1);
    }
}