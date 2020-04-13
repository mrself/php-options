<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Schema;

use Mrself\Options\Tests\Functional\TestCase;
use Mrself\Options\WithOptionsTrait;

class ArraySchemaTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testExceptionIsThrownIfRequiredOptionIsNotPassed()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1']
                ];
            }
        };
        $object->init();
    }

    public function testDefaultValueForOptionIsUsedIfValueIsNotProvided()
    {
        $object = new class {
            use WithOptionsTrait;

            public $option1;

            protected function getOptionsSchema()
            {
                return [
                    'defaults' => ['option1' => 'value1']
                ];
            }
        };
        $object->init();
        $this->assertEquals('value1', $object->option1);
    }
}