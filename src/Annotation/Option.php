<?php declare(strict_types=1);

namespace Mrself\Options\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Option
{
    /**
     * @var string
     */
    public $parameter = '';

    /**
     * If dependency should be retrieved from container. If false,
     * this options should be passed explicitly. Otherwise MissingOptionsException
     * will be thrown
     * @var bool
     */
    public $dependency = true;
}