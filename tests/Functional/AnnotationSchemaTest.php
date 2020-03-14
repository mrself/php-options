<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

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

    public function testPropertyDefaultValueIsUsed()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var string
             */
            public $option1 = 'str';
        };
        $object->init();
        $this->assertEquals('str', $object->option1);
    }

    public function testPropertyDefaultValueIsUsedForDependencyOption()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;
        };
        $object->option1 = new \DateTime();
        $object->init();
        $this->assertEquals(date('d'), $object->option1->format('d'));
    }

    public function testSchemaDefaultsAreUsed()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var \DateTime
             */
            public $option1;

            public function getOptionsSchema()
            {
                return [
                    'defaults' => [
                        'option1' => new \DateTime()
                    ]
                ];
            }
        };
        $object->init();
        $this->assertEquals(date('d'), $object->option1->format('d'));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testItDoNotRetrieveOptionOfPrimitiveTypeFromContainerIfSchemaExists()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var string
             */
            public $option1;

            public function getOptionsSchema()
            {
                return [
                    'allowedTypes' => [
                        'option1' => 'int'
                    ]
                ];
            }
        };
        $object->init();
        $this->assertEquals('str', $object->option1);
    }

    public function testAnnotationsCachesResult()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var string
             */
            public $option1 = 'str';
        };
        $object->init();
        $container = ContainerRegistry::get('App');
        // Set empty reader because the second time it should use cached result,
        // and not call reader again
        $container->set('app.annotation_reader', new class {}, true);
        $object->init(['option1' => 'str2']);
        $this->assertTrue(true);
    }

    public function testItUsedDefaultAnnotationsReaderIfAppContainerIsAbsent()
    {
        ContainerRegistry::reset();
        AnnotationRegistry::reset();
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option
             * @var string
             */
            public $option1 = 'str';
        };
        $object->init();
        $this->assertEquals('str', $object->option1);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testOptionIsRequiredIfAnnotationHasRequiredParameter()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(dependency=false)
             * @var \stdClass
             */
            public $option1;
        };
        $object->init();
    }

    public function testOptionsCanNotBePassedIfRequiredIsFalse()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(required=false)
             * @var \stdClass
             */
            public $option1;
        };
        $object->init();
        $this->assertTrue(true);
    }

    public function testOptionsCanBePassedIfRequiredIsFalse()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(required=false)
             * @var \stdClass
             */
            public $option1;
        };
        $object->init(['option1' => new \stdClass()]);
        $this->assertTrue(true);
    }

    public function testAnnotationsCanBeReadForPrivateProperty()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(required=false)
             * @var \stdClass
             */
            private $option1;
        };
        $object->init(['option1' => new \stdClass()]);
        $this->assertTrue(true);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCachedResultIsUsedForPropertiesMeta()
    {
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option()
             * @var \stdClass
             */
            private $option1;
        };
        $object->init(['option1' => new \stdClass()]);
        $object->init(['option1' => new \stdClass()]);
    }
}