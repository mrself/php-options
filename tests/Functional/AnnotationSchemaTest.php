<?php declare(strict_types=1);

namespace Mrself\Options\Tests;

use Mrself\Options\Annotation\Option;
use Mrself\Options\Options;
use Mrself\Options\Tests\Functional\DependencyContainerTrait;
use Mrself\Options\WithOptionsTrait;
use Mrself\Options\WithOptionsTrait1;
use PHPUnit\Framework\TestCase;

class AnnotationSchemaTest extends TestCase
{
    use DependencyContainerTrait;

    public function testOptionIsAcceptedOnlyOfAnnotatedType()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \Reflection
             */
            public $option1;
        };
        $reflection = new \Reflection();
        $object->init(['option1' => $reflection]);
        $this->assertEquals($reflection, $object->option1);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testOptionIsRejectedOfNotAnnotatedType()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \Reflection
             */
            public $option1;
        };
        $object->init(['option1' => 1]);
    }

    public function testTypeIsNotReadIfItIsPrimitive()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var string
             */
            public $option1;
        };
        $object->init(['option1' => 1]);
        $this->assertEquals(1, $object->option1);
    }

    public function testTypeIsNotReadIfItIsMultiple()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \Reflection|\ReflectionProperty
             */
            public $option1;
        };
        $object->init(['option1' => 1]);
        $this->assertEquals(1, $object->option1);
    }

    public function testTypeDoesNotRewriteExistingSchema()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \ReflectionProperty
             */
            public $option1;

            protected function getOptionsSchema()
            {
                return ['allowedTypes' => [
                    'option1' => \Reflection::class
                ]];
            }
        };
        $reflection = new \Reflection();
        $object->init(['option1' => $reflection]);
        $this->assertEquals($reflection, $object->option1);
    }
}