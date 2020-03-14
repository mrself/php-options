<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Options;

use DateTime as DateTime;
use Mrself\Options\Annotation\Option;
use Mrself\Options\Tests\Functional\TestCase;
use Mrself\Options\WithOptionsTrait;

class SilentTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testItDoesNotThrowIfSilentOptionExists()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return ['required' => ['option1']];
            }
        };
        $object->init(['.silent' => true]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testItDoesNotThrowIfOptionHasAnnotationType()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option()
             * @var DateTime
             */
            public $option;
        };
        $object->init(['.silent' => true]);
    }
}