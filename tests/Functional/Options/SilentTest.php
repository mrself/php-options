<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Options;

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
}