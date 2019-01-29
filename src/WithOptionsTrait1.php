<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Util\MiscUtil;
use PhpDocReader\PhpDocReader;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait WithOptionsTrait1
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    /**
     * @var array
     */
    protected $preOptions = [];

    protected $optionsSchema;

    public function resolveOptions(array $options = [])
    {
        $this->setPreOptions($options);
        $this->initOptionsSchema();
        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver->setDefaults($this->optionsSchema['defaults']);
        $this->optionsResolver->setRequired($this->optionsSchema['required']);
        foreach ($this->optionsSchema['allowedValues'] as $name => $values) {
            $this->optionsResolver->setAllowedValues($name, $values);
        }
        foreach ($this->optionsSchema['allowedTypes'] as $name => $types) {
            $this->optionsResolver->setAllowedTypes($name, $types);
        }
        foreach ($this->optionsSchema['normalizers'] as $name => $normalizer) {
            $this->optionsResolver->setNormalizer($name, $normalizer);
        }
        $this->fillDependencies();
        $this->options = $this->optionsResolver->resolve($this->preOptions);
    }

    public function onlyOptions(array $onlyKeys): array
    {
        return MiscUtil::only($this->options, $onlyKeys);
    }

    public function setPreOptions(array $options = [])
    {
        $this->preOptions = array_merge($this->preOptions, $options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionsSchema()
    {
        return [];
    }

    protected function initOptionsSchema()
    {
        $this->optionsSchema = array_merge_recursive([
            'required' => [],
            'allowedTypes' => [],
            'allowedValues' => [],
            'defaults' => [],
            'normalizers' => [],
            // @todo implement this
            'nested' => [],
        ], $this->getOptionsSchema());
        $this->addAnnotationOptionsSchema();
    }

    /**
     * @todo add support for multiple types like \Class1|\Class2
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    protected function addAnnotationOptionsSchema()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $properties = get_object_vars($this);
        $reader = new AnnotationReader();
        $docReader = new PhpDocReader();
        foreach ($properties as $property => $value) {
            $reflClass = new \ReflectionProperty($this, $property);
            $optionAnnotation = $reader->getPropertyAnnotation($reflClass, Option::class);
            /** @var $optionAnnotation Option */
            if ($optionAnnotation) {
                if (!in_array($reflClass->getName(), $this->optionsSchema['required'])) {
                    $this->optionsSchema['required'][] = $reflClass->getName();
                }
                if ($optionAnnotation->parameter) {
                    $parameterValue = Dependencies::getParameter($this->getDependencyContainerKey(), $optionAnnotation->parameter);
                    $this->optionsSchema['defaults'][$reflClass->getName()] = $parameterValue;
                    continue;
                }
                $type = $docReader->getPropertyClass($reflClass);
                if ($type && !array_key_exists($reflClass->getName(), $this->optionsSchema['allowedTypes'])) {
                    $this->optionsSchema['allowedTypes'][$reflClass->getName()] = $type;
                }
            }
        }
    }

    protected function fillDependencies()
    {
        foreach ($this->optionsSchema['allowedTypes'] as $name => $type) {
            if (array_key_exists($name, $this->preOptions)) {
                continue;
            }
            $this->preOptions[$name] = Dependencies::get($this->getDependencyContainerKey(), $type);
        }
    }

    protected function getDependencyContainerKey()
    {
        return 'app';
    }
}