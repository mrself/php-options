<?php declare(strict_types=1);

namespace Mrself\Options;

use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Init;
use Mrself\Options\Annotation\Option;
use Mrself\Util\MiscUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Options
{
    /**
     * @var WithOptionsTrait[]
     */
    protected static $sharedDependencies = [];

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

    /**
     * @var WithOptionsTrait
     */
    protected $owner;

    /**
     * @var string
     */
    protected $containerNamespace;

    /**
     * @var array
     */
    private $metaOptions = ['silent' => false];

    /**
     * Class of the owner
     * @var string
     */
    private $ownerClass;

    /**
     * @param array $options
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function resolve(array $options = [])
    {
        $this->init();
        $this->setPreOptions($options);
        $this->pullMetaOptions();
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

    protected function pullMetaOptions()
    {
        foreach ($this->preOptions as $option => $value) {
            if ($this->isOptionMeta($option)) {
                $this->addMetaOption($option, $value);
                unset($this->preOptions[$option]);
            }
        }
    }

    protected function addMetaOption(string $option, $value)
    {
        $option = $this->toMetaOption($option);
        $this->metaOptions[$option] = $value;
    }

    protected function toMetaOption(string $option): string
    {
        return str_replace('.', '', $option);
    }

    protected function isOptionMeta(string $option): bool
    {
        return $option[0] === '.';
    }

    public static function make(array $params)
    {
        $self = new static();
        $self->properties = $params['properties'];
        $self->owner = $params['owner'];
        $self->schema = $params['schema'];
        $self->preOptions = $params['preOptions'] ?: [];
        $self->containerNamespace = $params['containerNamespace'];
        $self->ownerClass = $params['class'];
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
            'init' => [],
            'asDependencies' => [],
            // @todo implement this
            'nested' => [],
        ], $this->getSchema(), $this->schema);
        $this->addAnnotationOptionsSchema();
        $this->processIgnoreNonPassed();
    }

    protected function processIgnoreNonPassed()
    {
        if (!$this->metaOptions['silent']) {
            return;
        }

        $this->schema['required'] = array_intersect(
            $this->schema['required'],
            array_keys($this->preOptions)
        );

        $this->schema['allowedTypes'] = array_intersect_assoc(
            $this->schema['allowedTypes'],
            $this->preOptions
        );
    }

    /**
     * @todo add support for multiple types like \Class1|\Class2
     *
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     * @throws \PhpDocReader\AnnotationException
     * @throws NonOptionableTypeException
     */
    protected function addAnnotationOptionsSchema()
    {
        $meta = PropertiesMeta::make([
            'objectClass' => $this->ownerClass,
            'properties' => $this->properties,
        ]);
        $meta->load();

        foreach ($meta->getByAnnotation(Option::class) as $metaDef) {
            $annotation = $metaDef->getAnnotation(Option::class);
            $this->processOptionAnnotation($metaDef, $annotation);
        }

        foreach ($meta->getByAnnotation(Init::class) as $metaDef) {
            $annotation = $metaDef->getAnnotation(Init::class);
            $this->processInitAnnotation($metaDef, $annotation);
        }
    }

    /**
     * @param PropertyMeta $meta
     * @param Init $annotation
     * @throws NonOptionableTypeException
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    protected function processInitAnnotation(PropertyMeta $meta, array $annotation)
    {
        $this->schema['required'][] = $meta->name;

        if (isset($this->preOptions[$meta->name])) {
            $this->defineAllowedType(
                $meta->name,
                $meta->getType(),
                $annotation
            );

            return;
        }

        /** @var WithOptionsTrait|string $type */
        $type = $meta->getType();

        $this->ensureClassUsesOptionableTrait($type);

        if ($annotation['shared']) {
            $dependency = $this->initDependency($type);
        } else {
            $dependency = $type::make();
        }
        $this->preOptions[$meta->name] = $dependency;
    }

    /**
     * @param WithOptionsTrait|string $type
     * @return mixed
     */
    protected function initDependency(string $type)
    {
        if (isset(static::$sharedDependencies[$type])) {
            return static::$sharedDependencies[$type];
        }

        return static::$sharedDependencies[$type] = $type::make();
    }

    /**
     * @param string $class
     * @throws NonOptionableTypeException
     */
    protected function ensureClassUsesOptionableTrait(string $class)
    {
        if (!OptionsUtil::isClassOptionable($class)) {
            throw new NonOptionableTypeException($class);
        }
    }

    protected function processOptionAnnotation(PropertyMeta $meta, $annotation)
    {
        $name = $meta->name;
        $isRequired = $this->defineRequired($name, $annotation);
        if (!$isRequired) {
            $this->defineDefault($name);
        }

        if ($this->defineParameter($name, @$annotation['parameter'])) {
            return;
        }

        $this->defineAllowedType(
            $name,
            $meta->getType(),
            $annotation
        );

        if (@$annotation['dependency']) {
            $this->schema['asDependencies'][] = $name;
        }
    }

    protected function hasDefault(string $name): bool
    {
        return !is_null($this->properties[$name]) ||
                array_key_exists($name, $this->schema['defaults']);
    }

    protected function defineDependencyType(string $name, string $type, $annotationRelated)
    {
        if (!$annotationRelated) {
            return $type;
        }

        $class = is_bool($annotationRelated) ? $name : $annotationRelated;
        return $this->owner->getRelatedClass($class);
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

            $initType = @$this->schema['init'][$name];
            if ($initType) {
                $this->preOptions[$name] = $initType::make();
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
            $this->preOptions[$name] = $this->getDependency($type[0]);
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
        $ownerClass = $this->ownerClass;
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
        $cachedContainer = Cache::getInstance()->silentGet('containers', $this->ownerClass);
        if ($cachedContainer) {
            return $cachedContainer;
        }

        $namespace = $this->getContainerNamespace();
        if (is_string($namespace)) {
            $namespace = explode('\\', $namespace);
        }
        $container = ContainerRegistry::get(implode('\\', $namespace), '');
        if ('' === $container) {
            $namespace[0];
            $container = ContainerRegistry::get($namespace[0], '');
        }
        $class = $this->ownerClass;
        if ('' === $container) {
            throw new UndefinedContainerException($namespace, $class);
        }

        Cache::getInstance()->set('containers', $this->ownerClass, $container);
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

    protected function isRequired(string $name): bool
    {
        return in_array($name, $this->schema['required']);
    }

    protected function defineParameter(string $name, $parameter): bool
    {
        if (!$parameter) {
            return false;
        }

        $parameterValue = $this->getParameter($parameter);
        $this->schema['defaults'][$name] = $parameterValue;
        return true;
    }

    protected function defineAllowedType(string $name, $type, $annotation)
    {
        if (!$type || isset($this->schema['allowedTypes'][$name])) {
            return;
        }

        $type = $this->defineDependencyType($name, $type, @$annotation['related']);
        $this->schema['allowedTypes'][$name] = [$type];
        if (!$annotation['required']) {
            $this->schema['allowedTypes'][$name][] = 'null';
        }
    }

    protected function defineDefault(string $name)
    {
        if (array_key_exists($name, $this->schema['defaults'])) {
            return;
        }

        $this->schema['defaults'][$name] = $this->properties[$name];
    }

    protected function defineRequired(string $name, $annotation)
    {
        if (!$annotation['required']) {
            return false;
        }

        if (in_array($name, $this->schema['required'])) {
            return false;
        }

        if ($this->hasDefault($name)) {
            return false;
        }

        $this->schema['required'][] = $name;
        return true;
    }

    public static function clearSharedDependencies()
    {
        static::$sharedDependencies = [];
    }

    /**
     * @param string $class
     * @param WithOptionsTrait $dependency
     */
    public static function addSharedDependency(string $class, $dependency)
    {
        static::$sharedDependencies[$class] = $dependency;
    }

    /**
     * @return WithOptionsTrait[]
     */
    public static function getSharedDependencies(): array
    {
        return static::$sharedDependencies;
    }
}