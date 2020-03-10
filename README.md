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
No DI, no 100-line constructors. Easy and quickly. Enjoy it.

If you have any questions feel free to contact me. I can guide you and demonstrate how you can use this package in your real project.

For more example see tests.
