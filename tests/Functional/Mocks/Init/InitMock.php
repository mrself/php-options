<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Mocks\Init;

use Mrself\Options\OptionableInterface;
use Mrself\Options\WithOptionsTrait;

class InitMock implements OptionableInterface
{
    use WithOptionsTrait;

    public $prop1;
}