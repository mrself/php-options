## Demo

Add the trait to your class

```php
class ClassWithOptions
{
    use \Mrself\Options\WithOptionsTrait;
}
```

Add the first option:

```php
use Mrself\Options\Annotation\Option;

class ClassWithOptions
{
    use \Mrself\Options\WithOptionsTrait;

    /**
    * The required option to initialize the class
     * @Option()
     * @var array
     */
    private $arrayOption;

    public function getOption()
    {
        return $this->arrayOption;
    }
}
```

Initialize the class:

```php
$instance = ClassWithOptions::make(['arrayOption' => ['key' => 'value']]);

// True
$instance->getOption()['key'] === 'value';
```

An exception is thrown if the option is of missed:

```php

// Exception \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
$instance = ClassWithOptions::make();
```

## More examples

Type resolving:

```php
use Mrself\Options\Annotation\Option;

class ClassWithOptions
{
    use \Mrself\Options\WithOptionsTrait;

    /**
     * @Option()
     * @var array
     */
    private $arrayOption;

    public function getOption()
    {
        return $this->arrayOption;
    }
}

$notArray = 1;
// Exception
ClassWithOptions::make(['arrayOption' => $notArray]);
```

```php
use Mrself\Options\Annotation\Option;

class ClassWithOptions
{
    use \Mrself\Options\WithOptionsTrait;

    /**
     * @Option()
     * @var \DateTime
     */
    private $arrayOption;

    public function getOption()
    {
        return $this->arrayOption;
    }
}

$notDate = 1;
// Exception
ClassWithOptions::make(['arrayOption' => $notDate]);
```

## Using with container (see mrself/container)

```php

use Mrself\Options\Annotation\Option;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;

$service = new \stdClass();
$service->property = 'myProperty';

$container = Container::make();
$container->set('service', $service);
ContainerRegistry::add('App', $container);

class ClassWithOptions
{
    use \Mrself\Options\WithOptionsTrait;

    /**
     * @Option()
     * @var \stdClass
     */
    private $service;

    public function getService()
    {
        return $this->service;
    }
}

$instance = ClassWithOptions::make();

// True
$instance->getService()->property === 'myProperty';
```

This trait can be used with Symfony or another framework with public services.

Suspend all errors:

```php
$object->init(['.silent' => true]);
```
---

If an annotated property has a non-primitive type, the property can be resolved only of that type:

```php
$object = new class {
    /**
     * @Option
     * @var \Reflection
     */
    public $option1;
};

// Throws since 'option1' expected a value of type '\Reflection'
$object->init(['option1' => 1]);
```
---

Primitive types are not processed so they should be declared in array schema:

```php
new class {
    protected function getOptionsSchema()
    {
        return [
            'allowedTypes' => ['option1' => \Reflection::class]
        ];
    }
 };
```

---

Array schema has a higher priority than an annotation schema

---

An option can be set as optional:

```php
$object = new class {
    /**
     * @Option(required=false)
     * @var \Reflection
     */
    public $option1;
};
```

---

Options can be preset by a specific key:

```php
$object = new class {

    /**
     * @Option()
     * @var string
     */
    public $option1;
};
$object::presetOptions('nameOfPreset', [
    'option1' => 'value1'
]);
$object->init(['presetName' => 'nameOfPreset']);
$object->option1 === 'value1';
```
