<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\OptionsClass;
use Mrself\Options\PropertiesMeta;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        OptionsClass::clearCache();
        PropertiesMeta::clearCache();
        ContainerRegistry::reset();
        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader('class_exists');
        $container = Container::make();
        $container->set('app.annotation_reader', new AnnotationReader());
        ContainerRegistry::add('App', $container);
    }
}