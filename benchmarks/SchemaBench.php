<?php declare(strict_types=1);

namespace Mrself\Options\Benches;

use DateTime;
use Mrself\Options\OptionsProvider;
use Mrself\Options\WithOptionsTrait;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Skip;

/**
 * @BeforeMethods({"init"})
 */
class SchemaBench
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
        ClassWithSchema::make(['option1' => new DateTime()]);
    }
}

class ClassWithSchema
{
    use WithOptionsTrait;

    /**
     * @var DateTime
     */
    private $option1;

    protected function getOptionsSchema()
    {
        return ['required' => ['option1']];
    }
}