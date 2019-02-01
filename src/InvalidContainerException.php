<?php declare(strict_types=1);

namespace Mrself\Options;

class InvalidContainerException extends OptionsException
{
    /**
     * @var mixed
     */
    protected $container;

    public function __construct($container)
    {
        $class = get_class($container);
        parent::__construct("Container of class '$class' does not match DependencyContainerInterface (it should not implement interface but only methods)'");
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }
}