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
}