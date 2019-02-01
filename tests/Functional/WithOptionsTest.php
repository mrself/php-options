<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Tests\Functional\Mocks\OptionsMock;
use Mrself\Options\WithOptionsTrait;
use PHPUnit\Framework\TestCase;

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
}