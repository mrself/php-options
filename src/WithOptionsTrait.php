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

    static protected $mock;

    /**
     * Saves last options when it is in mock mode
     * @var array
     */
    static protected $lastOptions;

    public static function mock($mock)
    {
        static::$mock = $mock;
    }

    public static function clearMock()
    {
        static::$mock = null;
        static::$lastOptions = null;
    }

    public static function make(array $options = []): self
    {
        if (static::$mock) {
            static::$lastOptions = $options;
            return static::$mock;
        }

        $self = new static();
        return $self->init($options);
    }

    public static function getLastOptions(): ?array
    {
        return static::$lastOptions;
    }

    /**
     * @param array $options
     * @return $this
     * @throws \Mrself\Options\UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
    public function init(array $options = [])
	{
        $this->resolveOptions($options);
        $this->onInit();
        return $this;
    }

    protected function onInit()
    {
    }

    public function setPreOptions(array $options = [])
    {
        $this->preOptions = array_merge($this->preOptions, $options);
    }

    /**
     * @param array $options
     * @throws UndefinedContainerException
     * @throws \Mrself\Container\Registry\NotFoundException
     */
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

    /**
     * @param array $keys
     * @return array
     * @throws \Mrself\Util\MiscUtil\AbsentKeyException
     * @throws \Mrself\Util\MiscUtil\InvalidSourceException
     */
    public function onlyOptions(array $keys)
    {
        return $this->options->only($keys);
    }

    public function getOptions(): array
    {
        return $this->options->getForOwner();
    }

    protected function makeOptions()
    {
        $optionsClass = $this->getOptionsClass($this);
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

    protected function getOptionsClass($object)
    {
        return OptionsClass::define($object);
    }
}