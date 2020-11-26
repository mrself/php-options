<?php declare(strict_types=1);

namespace Mrself\Options\Benches;

use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Options\Annotation\Option;
use Mrself\Options\Cache;
use Mrself\Options\OptionsProvider;
use Mrself\Options\WithOptionsTrait;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @BeforeMethods({"init"})
 */
class ContainerBench
{
    public function init()
    {
        (new OptionsProvider())->register();

        $container1 = Container::make();
        ContainerRegistry::add('namespace', $container1);

        $container2 = Container::make();
        ContainerRegistry::add('namespace2', $container2);

        $container1->addFallbackContainer($container2);

        $container2->set(Service1::class, new Service1());
        $container2->set(Service2::class, new Service2());
    }

    /**
     * @Revs(1000)
     * @Iterations(10)
     */
    public function benchOptionableWithServices()
    {
        OptionableWithServices::make();
    }
}

class OptionableWithServices
{
    use WithOptionsTrait;

    protected $optionsContainerNamespace = 'namespace\a';

    /**
     * @Option()
     * @var Service1
     */
    protected $service1;

    /**
     * @Option()
     * @var Service2
     */
    protected $service2;
}


class Service1 {}

class Service2 {}