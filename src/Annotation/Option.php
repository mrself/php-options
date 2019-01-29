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
}