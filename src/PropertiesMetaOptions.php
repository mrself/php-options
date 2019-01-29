<?php declare(strict_types=1);

namespace Mrself\Options;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpDocReader\PhpDocReader;

class PropertiesMetaOptions extends Options
{
    public function getSchema()
    {
        return [
            'required' => [
                'object',
                'properties',
                'annotationReader',
                'docReader'
            ],
            'allowedTypes' => [
                'properties' => 'array',
                'annotationReader' => AnnotationReader::class,
                'docReader' => PhpDocReader::class
            ]
        ];
    }
}