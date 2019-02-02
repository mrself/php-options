<?php declare(strict_types=1);

namespace Mrself\Options;

use PhpDocReader\PhpDocReader;

trait WithOptionsTrait
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var array
     */
    protected $preOptions = [];

    public static function make(array $options = []): self
    {
        $self = new static();
        return $self->init($options);
    }

	public function init(array $options = [])
	{
        $this->resolveOptions($options);
        return $this;
    }

    public function setPreOptions(array $options = [])
    {
        $this->preOptions = array_merge($this->preOptions, $options);
    }

    protected function resolveOptions(array $options = [])
    {
        $this->makeOptions();
        $this->options->resolve($options);
        foreach ($this->options->getForOwner() as $name => $value) {
            $this->$name = $value;
        }
        $this->onOptionsResolve();
    }

    protected function onOptionsResolve()
    {
    }

    protected function getOptionsSchema()
    {
        return [];
    }

    public function onlyOptions(array $keys)
    {
        return $this->options->only($keys);
    }

    protected function makeOptions()
    {
        $optionsClass = $this->getOptionsClass();
        $this->options = $optionsClass::make([
            'properties' => get_object_vars($this),
            'owner' => $this,
            'schema' => $this->getOptionsSchema(),
            'preOptions' => $this->preOptions,
            'containerNamespace' => $this->getOptionsContainerNamespace()
        ]);
    }

    public function getOptionsContainerNamespace(): string
    {
        if (property_exists($this, 'optionsContainerNamespace')) {
            return $this->optionsContainerNamespace;
        }
        return '';
    }

    protected function getOptionsClass()
    {
        $docReader = new PhpDocReader();
        $reflectionProperty = new \ReflectionProperty($this, 'options');
        return $docReader->getPropertyClass($reflectionProperty);
    }
}