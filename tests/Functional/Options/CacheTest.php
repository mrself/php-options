<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional\Options;

use Mrself\Options\Cache;
use Mrself\Options\Options;
use Mrself\Options\Tests\Functional\TestCase;

class CacheTest extends TestCase
{
    public function testCachedServiceIsUsedIfExists()
    {
        $options = Options::make([
            'properties' => [],
            'owner' => new class {},
            'schema' => [],
            'preOptions' => [],
            'containerNamespace' => '',
            'class' => 'classNamespace'
        ]);

        Cache::getInstance()->set('containers', 'classNamespace', 1);
        $this->assertEquals(1, $options->getContainer());
    }
}