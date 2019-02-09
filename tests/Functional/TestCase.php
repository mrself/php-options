<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        ContainerRegistry::reset();
        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader('class_exists');
        $container = new Container();
        $container->set('app.annotation_reader', new AnnotationReader());
        ContainerRegistry::add('App', $container);
    }
}