<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Mocks;

use Mrself\Options\Options;

class OptionsMock extends Options
{
    protected function getSchema()
    {
        return [
            'required' => ['option1']
        ];
    }
}