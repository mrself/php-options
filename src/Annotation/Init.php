<?php declare(strict_types=1);

namespace Mrself\Options\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotatin\Target
 */
class Init
{
    /**
     * If make class of provided instance with .make() method
     * @see WithOptionsTrait::make()
     * @var bool
     */
    public $make = false;

    /**
     * @var bool
     */
    public $shared = false;

    /**
     * @var boolean
     */
    public $required = true;
}