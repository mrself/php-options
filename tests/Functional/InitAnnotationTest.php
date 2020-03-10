<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Annotation\Init;
use Mrself\Options\Tests\Functional\Mocks\Init\InitMock;
use Mrself\Options\WithOptionsTrait;

class InitAnnotationTest extends TestCase
{
    public function testItMakesInstanceOfTheClassProvidedInVarAnnotationUsingMake()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Init()
             * @var InitMock
             */
            public $option1;
        };
        $object->init();
        $this->assertInstanceOf(InitMock::class, $object->option1);
    }

    /**
     * @expectedException \Mrself\Options\NonOptionableTypeException
     */
    public function testItThrowsIfOptionTypeClassDoesNotUseOptionsTrait()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Init()
             * Set type to StdClass as it does not use WithOptionsTrait
             * @var \stdClass
             */
            public $option1;
        };
        $object->init();
    }
}