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

    /**
     * @var array
     */
    protected static $presetOptions = [];

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
        $this->setPreOptions($options);
        $this->makeOptions();
        $this->options->resolve();
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

    public static function presetOptions(string $name, array $options)
    {
        static::$presetOptions[$name] = $options;
    }

    protected function makeOptions()
    {
        $optionsClass = $this->getOptionsClass($this);
        $schema = $this->getOptionsSchema();
        $this->addBuiltinSchema($schema);
        $this->options = $optionsClass::make([
            'properties' => get_object_vars($this),
            'owner' => $this,
            'schema' => $schema,
            'preOptions' => $this->getPreOptions(),
            'containerNamespace' => $this->getOptionsContainerNamespace()
        ]);
    }

    protected function getPreOptions(): array
    {
        $preOptions = $this->preOptions;
        if (array_key_exists('presetName', $preOptions) &&
            array_key_exists($preOptions['presetName'], static::$presetOptions)) {
            $preOptions = static::$presetOptions[$preOptions['presetName']] + $preOptions;
        }
        return $preOptions;
    }

    protected function addBuiltinSchema(array &$schema)
    {
        if (!array_key_exists('defaults', $schema)) {
            $schema['defaults'] = [];
        }
        $schema['defaults']['presetName'] = null;

        if (!array_key_exists('allowedTypes', $schema)) {
            $schema['allowedTypes'] = [];
        }
        $schema['allowedTypes']['presetName'] = ['string', 'null'];
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

    public function getRelatedClass(string $name)
    {
        $name = ucfirst($name);
        $selfName = ucfirst($this->getOptionsSelfName());
        $class = preg_replace(
            '/' . $selfName . '/',
            $name,
            $this->getClassName(),
            1
        );
        return str_replace($selfName, $name, $class);
    }

    protected function getClassName()
    {
        return static::class;
    }

    protected function getOptionsSelfName(): string
    {
        throw new \RuntimeException('Not implemented');
    }
}