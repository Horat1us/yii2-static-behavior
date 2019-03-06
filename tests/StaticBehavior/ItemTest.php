<?php

declare(strict_types=1);

namespace Horat1us\Yii\Tests\StaticBehavior;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Horat1us\Yii\StaticBehavior;
use yii\base;

/**
 * Class ItemTest
 * @package Horat1us\Yii\Tests\StaticBehavior
 */
class ItemTest extends TestCase
{
    /**
     * @covers \Horat1us\Yii\StaticBehavior\Item::init()
     */
    public function testInvalidHandlers(): void
    {
        /** @var MockObject|StaticBehavior\Item $item */
        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getMockBuilder(StaticBehavior\Item::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['init'])
            ->getMockForAbstractClass();

        $item->expects($this->once())
            ->method('handlers')
            ->with()
            ->willReturn([
                'string-handler' => '__invoke',
                'closure-handler' => function (): void {
                },
                'array-handler' => [$item, '__invoke',],
                'invalidMethodNameHandler' => 'invalid_method_name',
            ]);

        $this->expectExceptionMessage('Invalid handler configured for invalidMethodNameHandler event.');
        $this->expectException(base\InvalidConfigException::class);

        /** @noinspection PhpUnhandledExceptionInspection we expect this exception */
        $item->init();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Item::__invoke()
     */
    public function testInvokeInvalidEvent(): void
    {
        /** @var MockObject|StaticBehavior\Item $item */
        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getMockBuilder(StaticBehavior\Item::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['__invoke'])
            ->getMockForAbstractClass();

        $item->expects($this->once())
            ->method('handlers')
            ->with()
            ->willReturn([
                'event' => function () {
                },
            ]);

        $event = $this->mockEvent();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(get_class($item) . " can not handle event-name");

        $item($event);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Item::__invoke()
     */
    public function testInvokeStringMethod(): void
    {
        $event = $this->mockEvent();

        /** @var MockObject|StaticBehavior\Item $item */
        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getMockBuilder(StaticBehavior\Item::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['__invoke'])
            ->setMethods(['handleEvent'])
            ->getMockForAbstractClass();

        $item->expects($this->once())
            ->method('handlers')
            ->with()
            ->willReturn([
                $event->name => 'handleEvent',
            ]);

        $item->expects($this->once())
            ->method('handleEvent')
            ->with($event);

        $item($event);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Item::__invoke()
     */
    public function testInvokeCallable(): void
    {
        $event = $this->mockEvent();

        /** @var MockObject|StaticBehavior\Item $item */
        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getMockBuilder(StaticBehavior\Item::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['__invoke'])
            ->setMethods(['handleEventCallable'])
            ->getMockForAbstractClass();

        $item->expects($this->once())
            ->method('handlers')
            ->with()
            ->willReturn([
                $event->name => [$item, 'handleEventCallable',],
            ]);

        $item->expects($this->once())
            ->method('handleEventCallable')
            ->with($event);

        $item($event);
    }

    protected function mockEvent(): base\Event
    {
        return new base\Event([
            'name' => 'event-name',
        ]);
    }
}
