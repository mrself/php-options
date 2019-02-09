<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Options\UndefinedContainerException;
use Mrself\Options\WithOptionsTrait;

class DependenciesTest extends TestCase
{
    use DependencyContainerTrait;

    public function testDependencyIsTakenFromContainer()
    {
        $container = $this->getDependencyContainer();
        $dateTime = new \DateTime();
        $container->services['DateTime'] = $dateTime;

        $object = new class {
            use WithOptionsTrait;

            protected $optionsContainerNamespace = 'Mrself\\Options';

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        $object->init();
        $this->assertEquals($dateTime, $object->option1);
    }

    public function testExceptionIsThrownIfContainerIsAbsent()
    {
        $container = $this->getDependencyContainer();
        $dateTime = new \DateTime();
        $container->services['DateTime'] = $dateTime;

        $object = new class {
            use WithOptionsTrait;

            protected $optionsContainerNamespace = 'Mrself\\Options1';

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        try {
            $object->init();
        } catch (UndefinedContainerException $e) {
            $this->assertEquals('Mrself\\Options1', $e->getNamespace());
            $this->assertContains('class@anonymous', $e->getOwnerClass());
            return;
        }
        $this->assertTrue(false);
    }

    public function testOptionIsNotRetrievedFromContainerIfItPresentsAlready()
    {
        $container = $this->getDependencyContainer();
        $dateTime = new \DateTime();

        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        $object->setPreOptions(['option1' => $dateTime]);
        $object->init();
        $this->assertEquals($dateTime, $object->option1);
    }

    public function testParameterIsRetrievedFromContainerByKey()
    {
        $container = $this->getDependencyContainer();
        $container->parameters['param1'] = 'value1';
        $object = new class  {
            use WithOptionsTrait;

            protected $optionsContainerNamespace = 'Mrself\\Options';

            /**
             * @Option(parameter="param1")
             * @var \DateTime
             */
            public $option1;
        };
        $object->init();
        $this->assertEquals('value1', $object->option1);
    }

    public function testContainerNamespaceCanBeOnePart()
    {
        $container = $this->getDependencyContainer();
        ContainerRegistry::add('Mrself', $container);
        $dateTime = new \DateTime();
        $container->services['DateTime'] = $dateTime;

        $object = new class {
            use WithOptionsTrait;

            protected $optionsContainerNamespace = 'Mrself\\Options';

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        $object->init();
        $this->assertEquals($dateTime, $object->option1);
    }
}