<?php declare(strict_types=1);

namespace Mrself\Options\Annotation\Options;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Dependency
{
    /**
     * @var string
     */
    public $parameter = '';
}