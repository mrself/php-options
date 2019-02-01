<?php declare(strict_types=1);

namespace Mrself\Options;

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
     * @var PropertyMeta[]
     */
    protected $optionsMeta = [];

    /**
     * @var array
     */
    protected $properties;

    protected $owner;

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
            // @todo implement this
            'nested' => [],
        ], $this->getSchema(), $this->schema);
        $this->addAnnotationOptionsSchema();
    }

    /**
     * @todo add support for multiple types like \Class1|\Class2
     */
    protected function addAnnotationOptionsSchema()
    {
        $meta = PropertiesMeta::make([
            'object' => $this->owner,
            'properties' => $this->properties,
        ])->get();
        foreach ($meta as $name => $metaDef) {
            $optionAnnotation = $metaDef->getAnnotation(Option::class);
            /** @var $optionAnnotation Option */
            if (!$optionAnnotation) {
                continue;
            }
            $this->optionsMeta[$name] = $metaDef;
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
        }
    }

    protected function fillDependencies()
    {
        foreach ($this->schema['allowedTypes'] as $name => $type) {
            if (array_key_exists($name, $this->preOptions)) {
                continue;
            }
            if ($this->isPrimitiveType($type)) {
                continue;
            }
            $this->preOptions[$name] = $this->getDependency($type);
        }
    }

    protected function getDependency(string $type)
    {
        return Dependencies::get($this->getDependencyContainerKey(), $type);
    }

    protected function getParameter(string $name)
    {
        return Dependencies::getParameter($this->getDependencyContainerKey(), $name);
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
}