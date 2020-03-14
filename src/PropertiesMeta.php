<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Registry\ContainerRegistry;
use PhpDocReader\PhpDocReader;


class PropertiesMeta
{
    /**
     *
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     *
     * @var PhpDocReader
     */
    protected $docReader;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var mixed
     */
    protected $object;

    /**
     * @var PropertiesMetaOptions
     */
    protected $options;

    static private $cache = [];

    /**
     * Meta data of properties
     * @see PropertiesMeta::$properties
     * @var array
     */
    protected $meta = [];

    public static function make(array $options)
    {
        $self = new static();
        foreach ($options as $key => $value) {
            $self->$key = $value;
        }
        $self->init();
        return $self;
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function init()
    {
        if (ContainerRegistry::has('App')) {
            $this->annotationReader = ContainerRegistry::get('App')
                ->get('app.annotation_reader');
        } else {
            AnnotationRegistry::reset();
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new AnnotationReader();
        }

        $this->docReader = new PhpDocReader();
    }

    /**
     * @throws \PhpDocReader\AnnotationException
     */
    public function load()
    {
        $class = get_class($this->object);
        if (static::hasCache($class)) {
            $this->meta = self::getCached($class);
        } else {
            $this->runLoad($class);
        }
    }

    /**
     * @param string $class
     * @throws \PhpDocReader\AnnotationException
     */
    private function runLoad(string $class)
    {
        foreach ($this->properties as $name => $value) {
            try {
                $reflection = new \ReflectionProperty(get_class($this->object), $name);
            } catch (\ReflectionException $e) {
                continue;
            }
            $annotations = $this->annotationReader->getPropertyAnnotations($reflection);
            $type = $this->docReader->getPropertyClass($reflection);
            $options = compact('type', 'annotations','name', 'reflection');
            $this->meta[$name] = PropertyMeta::make($options);
        }

        static::addCache($class, $this->meta);
    }

    public function getByAnnotation(string $annotationClass): array
    {
        $result = [];
        foreach ($this->meta as $item) {
            if ($item->getAnnotation($annotationClass)) {
                $result[] = $item;
            }
        }
        return $result;
    }

    static public function clearCache()
    {
        static::$cache = [];
    }

    static private function getCached(string $class)
    {
        return static::$cache[$class];
    }

    static private function addCache(string $class, array $meta)
    {
        static::$cache[$class] = $meta;
    }

    static private function hasCache(string $class)
    {
        return array_key_exists($class, static::$cache);
    }
}