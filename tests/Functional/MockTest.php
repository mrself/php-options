<?php declare(strict_types=1);

namespace Mrself\Options\Tests\Functional;

use Mrself\Options\Tests\Functional\Mocks\WithOptionsMock;
use Mrself\Options\WithOptionsTrait;
use PHPUnit\Framework\MockObject\MockObject;

class MockTest extends TestCase
{
    public function testMockIsUsedIfProvided()
    {
        $mock = $this->createMock(WithOptionsMock::class);
        WithOptionsMock::mock($mock);
        $this->assertInstanceOf(MockObject::class, WithOptionsMock::make());
    }

    public function testClearMock()
    {
        $mock = $this->createMock(WithOptionsMock::class);
        WithOptionsMock::mock($mock);
        WithOptionsMock::clearMock();
        $this->assertNotInstanceOf(MockObject::class, WithOptionsMock::make());
    }

    public function testLastOptionsAreSetWhenMakingWithMock()
    {
        $mock = $this->createMock(WithOptionsMock::class);
        WithOptionsMock::mock($mock);
        WithOptionsMock::make(['key' => 'value']);
        $this->assertEquals(['key' => 'value'], WithOptionsMock::getLastOptions());
    }
}