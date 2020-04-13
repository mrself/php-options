<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Util\ArrayUtil;
use PhpDocReader\PhpDocReader;


class PropertiesMeta
{
    /**
     * @var PhpDocReader
     */
    private static $docReader;
    /**
     *
     * @var AnnotationReader
     */
    protected $annotationReader;

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

    public static function register(PhpDocReader $reader)
    {
        static::$docReader = $reader;
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
    }

    /**
     * @throws \PhpDocReader\AnnotationException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function load()
    {
        $class = get_class($this->object);
        $cacheId = 'mrself/options:' . $class;

        $container = ContainerRegistry::get('Mrself\Options', null);
        if (!$container || !$container->get('cache', null)) {
            if (static::hasCache($cacheId)) {
                $this->meta = self::getCached($cacheId);
            } else {
                $this->runLoad($class, $cacheId);
            }
            return;
        }

        /** @var Memcached $memcached */
        $memcached = $container->get('cache');
        $cached = $memcached->get($cacheId);
        if ($cached) {
            $this->meta = $cached;
        } else {
            $this->runLoad($class, $cacheId);
            $memcached->set($cacheId, $this->meta);
        }
    }

    /**
     * @param string $class
     * @throws \PhpDocReader\AnnotationException
     */
    private function runLoad(string $class, string $cacheId)
    {
        foreach ($this->properties as $name => $value) {
            try {
                $reflection = new \ReflectionProperty(get_class($this->object), $name);
            } catch (\ReflectionException $e) {
                continue;
            }
            $annotations = $this->getAnnotations($reflection);
            $type = $this->getDocReader()->getPropertyClass($reflection);
            $options = compact('type', 'annotations','name');
            $this->meta[$name] = PropertyMeta::make($options);
        }

        static::addCache($cacheId, $this->meta);
    }

    private function getAnnotations(\ReflectionProperty $reflection)
    {
        $annotations = $this->annotationReader->getPropertyAnnotations($reflection);
        return ArrayUtil::map($annotations, function ($annotationObj) {
            $array = (array) $annotationObj;
            $array['class'] = get_class($annotationObj);
            return $array;
        });
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

    private function getDocReader()
    {
        if (static::$docReader) {
            return static::$docReader;
        }

        return new PhpDocReader();
    }
}