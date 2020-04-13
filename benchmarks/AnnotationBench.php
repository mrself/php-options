<?php declare(strict_types=1);

namespace Mrself\Options\Benches;

use DateTime;
use Mrself\Options\Annotation\Option;
use Mrself\Options\OptionsProvider;
use Mrself\Options\WithOptionsTrait;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @BeforeMethods({"init"})
 */
class AnnotationBench
{
    public function init()
    {
        (new OptionsProvider())->register();
    }

    /**
     * @Revs(1000)
     */
    public function benchAnnotation()
    {
        ClassWithAnnotation::make(['option1' => new DateTime()]);
    }
}

class ClassWithAnnotation
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var DateTime
     */
    private $option1;
}