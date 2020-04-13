<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;
use PhpDocReader\PhpDocReader;

class OptionsProvider
{
    public function register()
    {
        $container = Container::make();
        $container->set('cache', new Memcached());
        ContainerRegistry::add('Mrself\\Options', $container);

        if (ContainerRegistry::has('App')) {
            $appContainer = ContainerRegistry::get('App');
            $annotationReader = $appContainer->get('app.annotation_reader');
        } else {
            AnnotationRegistry::reset();
            AnnotationRegistry::registerLoader('class_exists');
            $annotationReader = new AnnotationReader();
        }
        $cache = null;

        $container->set('annotation_reader', $annotationReader);
        $container->set('doc_reader', new PhpDocReader());
    }
}