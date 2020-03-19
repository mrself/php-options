<?php declare(strict_types=1);

namespace Mrself\Options;

class NonOptionableTypeException extends OptionsException
{
    public function __construct(string $class)
    {
        parent::__construct('The class pointed with @Init annotation does not implements OptionableInterface. Only classes implementing it are processed with @Init annotation');
    }
}