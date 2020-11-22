<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Mocks\Init;

use Mrself\Options\OptionableInterface;
use Mrself\Options\WithOptionsTrait;
use Psr\Log\LoggerInterface;

class ClassWithTwoInterfaces implements LoggerInterface, OptionableInterface
{
    use WithOptionsTrait;

    public $prop = 1;

    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
    }

    public function alert($message, array $context = array())
    {
        // TODO: Implement alert() method.
    }

    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
    }

    public function error($message, array $context = array())
    {
        // TODO: Implement error() method.
    }

    public function warning($message, array $context = array())
    {
        // TODO: Implement warning() method.
    }

    public function notice($message, array $context = array())
    {
        // TODO: Implement notice() method.
    }

    public function info($message, array $context = array())
    {
        // TODO: Implement info() method.
    }

    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }

}