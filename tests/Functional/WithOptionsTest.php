<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Tests\Functional\Mocks\OptionsMock;
use Mrself\Options\Tests\Functional\Mocks\WithOptionsMock;
use Mrself\Options\WithOptionsTrait;
use PHPUnit\Framework\MockObject\MockObject;

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
        $this->assertEquals(['option1' => 'value1'], $object->_getOptions());
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
        $this->assertEquals(['option1' => false], $object->_options());
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
        $this->assertEquals(['option1' => 1], $object->_options());
    }

    public function testMockIsUsedIfProvided()
    {
        $mock = $this->createMock(WithOptionsMock::class);
        WithOptionsMock::mock($mock);
        $this->assertInstanceOf(MockObject::class, WithOptionsMock::make());
    }

    public function testClearMock()
    {
        $mock = $this->createMock(WithOptionsMock::class);
        WithOptionsMock::mock($mock);
        WithOptionsMock::clearMock();
        $this->assertNotInstanceOf(MockObject::class, WithOptionsMock::make());
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
}