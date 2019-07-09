<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Tests\Functional\Mocks\OptionsMock;
use Mrself\Options\WithOptionsTrait;

class WithOptionsTest extends TestCase
{
    public function testItWorks()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @var OptionsMock
             */
            protected $options;

            public function _getOptions()
            {
                return $this->options->getForOwner();
            }
        };
        $object->init(['option1' => 'value1']);
        $this->assertEquals('value1', $object->_getOptions()['option1']);
    }

    public function testOptionsIsNotResolvedAsServiceIfItIsNotRequired()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @var \Reflection
             */
            protected $option2;

            protected function getOptionsSchema()
            {
                return [
                    'allowedTypes' => [
                        'option1' => [\stdClass::class, 'bool']
                    ],
                    'defaults' => [
                        'option1' => false
                    ]
                ];
            }

            public function _options()
            {
                return $this->options->getForOwner();
            }
        };
        $object->init([]);
        $this->assertFalse($object->_options()['option1']);
    }

    public function testPreOptionsAreUsed()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1']
                ];
            }

            public function _options()
            {
                return $this->options->getForOwner();
            }
        };
        $object->setPreOptions(['option1' => 1]);
        $object->init();
        $this->assertEquals(1, $object->_options()['option1']);
    }

    public function testItWorksWithDynamicProperties()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1']
                ];
            }

            public function _options()
            {
                return $this->options->getForOwner();
            }
        };
        $object->dynamicProperty = 1;
        $object->init(['option1' => 1]);
        $this->assertTrue(true);
    }

    public function testAllowedTypesCanBeUsedInArray()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'allowedTypes' => [
                        'option1' => ['array', 'boolean']
                    ],
                    'required' => ['option1']
                ];
            }
        };
        $object->init(['option1' => true]);
        $this->assertTrue(true);
    }
}