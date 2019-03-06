<?php

declare(strict_types=1);

namespace Horat1us\Yii\Tests\StaticBehavior;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Horat1us\Yii\StaticBehavior;
use yii\base;
use yii\console;

/**
 * Class BootstrapTest
 * @package Horat1us\Yii\Tests\StaticBehavior
 */
class BootstrapTest extends TestCase
{
    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap::init()
     */
    public function testInvalidConfiguration(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        new StaticBehavior\Bootstrap([
            'behaviors' => [
                new \stdClass(),
            ],
        ]);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap::init()
     */
    public function testInstantiating(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->getMock();

        $bootstrap = new StaticBehavior\Bootstrap([
            'behaviors' => [
                'mock' => get_class($behavior),
            ],
        ]);


        $this->assertInstanceOf(
            get_class($behavior),
            $bootstrap->behaviors['mock']
        );
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap::attach()
     */
    public function testAttaching(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $firstBehavior = $this->createMock(StaticBehavior::class);
        $firstBehavior->expects($this->once())
            ->method('attach');

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondBehavior = $this->createMock(StaticBehavior::class);
        $secondBehavior->expects($this->once())
            ->method('attach');

        $bootstrap = new StaticBehavior\Bootstrap([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $bootstrap->attach();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap::detach()
     */
    public function testDetaching(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $firstBehavior = $this->createMock(StaticBehavior::class);
        $firstBehavior->expects($this->once())
            ->method('detach');

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondBehavior = $this->createMock(StaticBehavior::class);
        $secondBehavior->expects($this->once())
            ->method('detach');

        $bootstrap = new StaticBehavior\Bootstrap([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $bootstrap->detach();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap::bootstrap()
     */
    public function testBootstrap(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->createMock(StaticBehavior::class);
        $bootstrap = new StaticBehavior\Bootstrap([
            'behaviors' => [$behavior,],
        ]);

        /** @var console\Application|MockObject $application */
        /** @noinspection PhpUnhandledExceptionInspection */
        $application = $this->createMock(console\Application::class);

        $application->expects($this->at(0))
            ->method('on')
            ->with(base\Application::EVENT_BEFORE_REQUEST, [$bootstrap, 'attach']);
        $application->expects($this->at(1))
            ->method('on')
            ->with(base\Application::EVENT_AFTER_REQUEST, [$bootstrap, 'detach']);

        $bootstrap->bootstrap($application);
    }
}
