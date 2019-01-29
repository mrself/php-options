<?php declare(strict_types=1);

namespace Mrself\Options\Tests;

use Mrself\Options\Annotation\Option;
use Mrself\Options\Dependencies;
use Mrself\Options\DependencyContainerInterface;
use Mrself\Options\Options;
use Mrself\Options\Tests\Functional\DependencyContainerTrait;
use Mrself\Options\WithOptionsTrait;
use Mrself\Options\WithOptionsTrait1;
use PHPUnit\Framework\TestCase;

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

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        $object->run();
        $this->assertEquals($dateTime, $object->option1);
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
        $object->run();
        $this->assertEquals($dateTime, $object->option1);
    }

    public function testParameterIsRetrievedFromContainerByKey()
    {
        $container = $this->getDependencyContainer();
        $container->parameters['param1'] = 'value1';
        $object = new class  {
            use WithOptionsTrait;

            /**
             * @Option(parameter="param1")
             * @var \DateTime
             */
            public $option1;
        };
        $object->run();
        $this->assertEquals('value1', $object->option1);
    }
}