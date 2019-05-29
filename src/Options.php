<?php declare(strict_types=1);

namespace Mrself\Options;

use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Util\MiscUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Options
{
    /**
     * @var array
     */
    protected $resolved;

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    /**
     * @var array
     */
    protected $preOptions = [];

    protected $schema;

    /**
     * @var array
     */
    protected $properties;

    protected $owner;

    /**
     * @var string
     */
    protected $containerNamespace;

    /**
     * @param array $options
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function resolve(array $options = [])
    {
        $this->init();
        $this->setPreOptions($options);
        $this->initSchema();
        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver->setDefaults($this->schema['defaults']);
        $this->optionsResolver->setRequired($this->schema['required']);
        foreach ($this->schema['allowedValues'] as $name => $values) {
            $this->optionsResolver->setAllowedValues($name, $values);
        }
        foreach ($this->schema['allowedTypes'] as $name => $types) {
            $this->optionsResolver->setAllowedTypes($name, $types);
        }
        $this->fillDependencies();
        $this->resolved = $this->optionsResolver->resolve($this->preOptions);
        $this->normalize();
    }

    public static function make(array $params)
    {
        $self = new static();
        $self->properties = $params['properties'];
        $self->owner = $params['owner'];
        $self->schema = $params['schema'];
        $self->preOptions = $params['preOptions'] ?: [];
        $self->containerNamespace = $params['containerNamespace'];
        return $self;
    }

    public function init()
    {
    }

    public function getForOwner(): array
    {
        return $this->resolved;
    }

    /**
     * @param array $onlyKeys
     * @return array
     * @throws MiscUtil\AbsentKeyException
     * @throws MiscUtil\InvalidSourceException
     */
    public function only(array $onlyKeys): array
    {
        return MiscUtil::only($this->resolved, $onlyKeys);
    }

    public function setPreOptions(array $options = [])
    {
        $this->preOptions = array_merge($this->preOptions, $options);
    }

    protected function getSchema()
    {
        return [];
    }

    public function setParentProps(array $properties, $owner)
    {
        $this->properties = $properties;
        $this->owner = $owner;
    }

    protected function initSchema()
    {
        $this->schema = array_replace_recursive([
            'required' => [],
            'allowedTypes' => [],
            'allowedValues' => [],
            'defaults' => [],
            'normalizers' => [],
            'omitForOwner' => [],
            'locals' => [],
            'asDependencies' => [],
            // @todo implement this
            'nested' => [],
        ], $this->getSchema(), $this->schema);
        $this->addAnnotationOptionsSchema();
    }

    /**
     * @todo add support for multiple types like \Class1|\Class2
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     * @throws \PhpDocReader\AnnotationException
     */
    protected function addAnnotationOptionsSchema()
    {
        $meta = PropertiesMeta::make([
            'object' => $this->owner,
            'properties' => $this->properties,
        ])->get();
        foreach ($meta as $name => $metaDef) {
            $optionAnnotation = $metaDef->getAnnotation(Option::class);
            if (!$optionAnnotation) {
                continue;
            }
            if (!in_array($name, $this->schema['required'])) {
                $this->schema['required'][] = $name;
            }
            if ($optionAnnotation->parameter) {
                $parameterValue = $this->getParameter($optionAnnotation->parameter);
                $this->schema['defaults'][$name] = $parameterValue;
                continue;
            }
            $type = $metaDef->getType();
            if ($type && !array_key_exists($name, $this->schema['allowedTypes'])) {
                $this->schema['allowedTypes'][$name] = $type;
            }
            if (!is_null($this->properties[$name])) {
                $this->schema['defaults'][$name] = $this->properties[$name];
            }

            if ($optionAnnotation->dependency) {
                $this->schema['asDependencies'][] = $name;
            }
        }
    }

    /**
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    protected function fillDependencies()
    {
        foreach ($this->schema['allowedTypes'] as $name => $type) {
            if (!in_array($name, $this->schema['required'])) {
                continue;
            }
            if (array_key_exists($name, $this->preOptions)) {
                continue;
            }
            if ($this->isPrimitiveTypes($type)) {
                continue;
            }
            if (!in_array($name, $this->schema['asDependencies'])) {
                continue;
            }
            if (array_key_exists($name, $this->schema['defaults'])) {
                continue;
            }
            $this->preOptions[$name] = $this->getDependency($type);
        }
    }

    /**
     * @param string $type
     * @return mixed
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    protected function getDependency(string $type)
    {
        return $this->getContainer()->get($type);
    }

    /**
     * @return string|string[]
     */
    protected function getContainerNamespace()
    {
        if ($this->containerNamespace) {
            return $this->containerNamespace;
        }
        $ownerClass = get_class($this->owner);
        $parts = explode('\\', $ownerClass);
        return array_slice($parts, 0, 2);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    protected function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @return mixed
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function getContainer()
    {
        $namespace = $this->getContainerNamespace();
        if (is_string($namespace)) {
            $namespace = explode('\\', $namespace);
        }
        $container = ContainerRegistry::get(implode('\\', $namespace), '');
        if ('' === $container) {
            $namespace[0];
            $container = ContainerRegistry::get($namespace[0], '');
        }
        $class = get_class($this->owner);
        if ('' === $container) {
            throw new UndefinedContainerException($namespace, $class);
        }
        return $container;
    }

    protected function getDependencyContainerKey()
    {
        return 'app';
    }

    public function normalize()
    {
        foreach ($this->schema['normalizers'] as $name => $normalizer) {
            $this->resolved[$name] = $normalizer($this->resolved[$name], $this->resolved);
        }
    }

    protected function isPrimitiveType(string $typeName)
    {
        return in_array($typeName, [
            'int',
            'integer',
            'bool',
            'boolean',
            'float',
            'string',
            'array',
            'double'
        ]);
    }

    protected function isPrimitiveTypes($types)
    {
        $types = (array) $types;
        return count(array_filter($types, [$this, 'isPrimitiveType']));
    }
}