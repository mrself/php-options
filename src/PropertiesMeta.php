<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Util\ArrayUtil;
use PhpDocReader\PhpDocReader;


class PropertiesMeta
{
//    use WithOptionsTrait1;

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

    public static function make(array $options)
    {
        $self = new static();
        foreach ($options as $key => $value) {
            $self->$key = $value;
        }
        $self->init();
        return $self;
    }

    public function init()
    {
        // @todo add exception when 'App' container or app reader not found
        $this->annotationReader = ContainerRegistry::get('App')
            ->get('app.annotation_reader');
        $this->docReader = new PhpDocReader();
    }

    /**
     * @return PropertyMeta[]
     */
    public function get(): array
    {
        return ArrayUtil::map($this->properties, function ($value, string $name) {
            $reflection = new \ReflectionProperty($this->object, $name);
            $annotations = $this->annotationReader->getPropertyAnnotations($reflection);
            $type = $this->docReader->getPropertyClass($reflection);
            $options = compact('type', 'annotations','name', 'reflection');
            return PropertyMeta::make($options);
        });
    }
}