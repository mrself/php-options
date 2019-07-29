<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\AnnotationSchema;

use Mrself\Options\Annotation\Option;
use Mrself\Options\Tests\Functional\DependencyContainerTrait;
use Mrself\Options\Tests\Functional\TestCase;
use Mrself\Options\WithOptionsTrait;

class RelatedPropertyTest extends TestCase
{
    use DependencyContainerTrait;

    public function testItUsesClassPropertyNameAsRelatedClassNameForDependencyIfRelatedIsTrue()
    {
        $container = $this->getDependencyContainer();
        $container->set('App\\Repository\\ProductRepository', new ProductRepository());
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(related=true)
             * @var \stdClass
             */
            public $repository;

            protected function getClassName()
            {
                return 'App\\Model\\ProductModel';
            }

            protected function getOptionsSelfName(): string
            {
                return 'model';
            }

            protected function getOptionsContainerNamespace(): string
            {
                return 'Mrself\\Options';
            }
        };
        $object->init();
        $this->assertInstanceOf(ProductRepository::class, $object->repository);
    }

    public function testItUsesClassPropertyNameAsRelatedClassNameForDependencyIfRelatedIsString()
    {
        $container = $this->getDependencyContainer();
        $container->set('App\\Repository\\ProductRepository', new ProductRepository());
        $object = new class {
            use WithOptionsTrait;

            /**
             * @Option(related="repository")
             * @var \stdClass
             */
            public $repository1;

            protected function getClassName()
            {
                return 'App\\Model\\ProductModel';
            }

            protected function getOptionsSelfName(): string
            {
                return 'model';
            }

            protected function getOptionsContainerNamespace(): string
            {
                return 'Mrself\\Options';
            }
        };
        $object->init();
        $this->assertInstanceOf(ProductRepository::class, $object->repository1);
    }

    protected function setUp()
    {
        parent::setUp();
        if (!class_exists('App\\Repository\\ProductRepository')) {
            class_alias(ProductRepository::class, 'App\\Repository\\ProductRepository');
        }
    }
}

class ProductRepository
{

}