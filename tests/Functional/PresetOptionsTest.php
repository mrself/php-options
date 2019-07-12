<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\WithOptionsTrait;

class PresetOptionsTest extends TestCase
{
    public function testPresetOptionsCanBeSet()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1']
                ];
            }
        };
        $object::presetOptions('name', [
            'option1' => 'value1'
        ]);
        $object->init(['presetName' => 'name']);
        $this->assertEquals('value1', $object->getOptions()['option1']);
    }

    public function testPresetNameCanBeSetWithoutPresetOptionsSet()
    {
        $object = new class {
            use WithOptionsTrait;

            protected function getOptionsSchema()
            {
                return [
                    'required' => ['option1']
                ];
            }
        };
        $object->init(['presetName' => 'name', 'option1' => 'value1']);
        $this->assertEquals('value1', $object->getOptions()['option1']);
    }
}