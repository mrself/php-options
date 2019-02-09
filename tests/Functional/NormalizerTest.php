<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\WithOptionsTrait;

class NormalizerTest extends TestCase
{
    use DependencyContainerTrait;

    public function testNormalizersAreCalledWhenAllOptionsAreResolved()
    {
        $container = $this->getDependencyContainer();
        $container->services['Reflection'] = new \Reflection();
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1', 'option2'],
                    'normalizers' => [
                        'option2' => function ($value, $options) {
                            return $options['option1'] . $value;
                        }
                    ]
                ];
            }
        };
        $object->init([
            'option1' => 'value1',
            'option2' => 'value2',
        ]);
        $expected = [
            'option1' => 'value1',
            'option2' => 'value1value2',
        ];
        $this->assertEquals('value1', $object->option1);
        $this->assertEquals('value1value2', $object->option2);
    }
}