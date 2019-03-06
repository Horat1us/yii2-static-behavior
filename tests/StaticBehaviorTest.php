<?php

declare(strict_types=1);

namespace Horat1us\Yii\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Horat1us\Yii\StaticBehavior;
use yii\base;

/**
 * Class StaticBehaviorTest
 * @package Horat1us\Yii\Tests
 */
class StaticBehaviorTest extends TestCase
{
    /**
     * @covers \Horat1us\Yii\StaticBehavior::init()
     */
    public function testInvalidTarget(): void
    {
        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['init'])
            ->getMock();

        $behavior->target = null;

        $this->expectExceptionMessage('Class name have to be specified as string, NULL given.');
        $this->expectExceptionCode(1);
        $this->expectException(base\InvalidConfigException::class);

        /** @noinspection PhpUnhandledExceptionInspection  we expects this exception */
        $behavior->init();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::init()
     */
    public function testInvalidItemReference(): void
    {
        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['init'])
            ->getMock();

        $behavior->target = base\Component::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior->items = [
            'closure' => function (): void {
            },
            'array-reference' => [
                'class' => base\Component::class,
            ],
            'string-reference' => base\Component::class,
            'object-reference' => $this->getMockBuilder(StaticBehavior\ItemInterface::class)->getMock(),
            'invalid-class-name' => 'SomeInvalidClassName',
        ];

        $this->expectExceptionMessage('Invalid reference for item invalid-class-name');
        $this->expectExceptionCode(2);
        $this->expectException(base\InvalidConfigException::class);

        /** @noinspection PhpUnhandledExceptionInspection we expects this exception */
        $behavior->init();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::items()
     */
    public function testItems(): void
    {
        $items = [\stdClass::class,];
        $behavior = new StaticBehavior([
            'target' => \stdClass::class,
            'items' => $items,
        ]);

        $this->assertEquals(
            $items,
            $behavior->items()
        );
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::handle()
     */
    public function testHandleUnsupportedEvent(): void
    {
        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['handle', 'init',])
            ->getMock();

        $behavior->target = base\Component::class;

        $event = new base\Event([
            'name' => 'unsupported-event',
        ]);

        $this->expectExceptionMessage(
            get_class($behavior) . " can not handle yii\base\Component unsupported-event event."
        );
        $this->expectException(\InvalidArgumentException::class);

        /** @noinspection PhpUnhandledExceptionInspection will fail before container */
        $behavior->handle($event);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::handle()
     */
    public function testHandlingEventCallable(): void
    {
        $event = new base\Event([
            'name' => 'closure-event',
        ]);

        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['handle',])
            ->setMethods(['handleEvent', 'items',])
            ->getMock();

        $behavior->expects($this->once())
            ->method('handleEvent')
            ->with($event)
            ->willReturn(null);

        $behavior->target = base\Component::class;
        $behavior->expects($this->once())
            ->method('items')
            ->with()
            ->willReturn([
                $event->name => \Closure::fromCallable([$behavior, 'handleEvent',]),
            ]);

        /** @noinspection PhpUnhandledExceptionInspection everything should be ok */
        $behavior->handle($event);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::handle()
     */
    public function testHandlingEventItem(): void
    {
        $event = new base\Event([
            'name' => 'item-event',
        ]);

        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['handle',])
            ->setMethods(['items',])
            ->getMock();

        /** @var StaticBehavior\ItemInterface|MockObject $item */
        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getMockBuilder(StaticBehavior\ItemInterface::class)->getMock();
        $item->expects($this->once())
            ->method('__invoke')
            ->with($event)
            ->willReturn(0);

        $behavior->target = base\Component::class;
        $behavior->expects($this->once())
            ->method('items')
            ->with()
            ->willReturn([
                $event->name => $item,
            ]);

        /** @noinspection PhpUnhandledExceptionInspection everything should be ok */
        $behavior->handle($event);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::detach()
     */
    public function testDetach(): void
    {
        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['detach',])
            ->setMethods(['items', 'off',])
            ->getMock();

        $behavior->expects($this->once())
            ->method('items')
            ->with()
            ->willReturn([
                'closure' => $closure = function (): void {
                },
                'method' => $methodName = 'items',
            ]);

        $behavior->expects($this->at(1))
            ->method('off')
            ->with('closure', $closure)
            ->willReturn(null);
        $behavior->expects($this->at(2))
            ->method('off')
            ->with('method', [$behavior, 'handle',])
            ->willReturn(null);

        $behavior->detach();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::attach()
     */
    public function testAttach(): void
    {
        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['detach',])
            ->setMethods(['items', 'on',])
            ->getMock();

        $behavior->expects($this->once())
            ->method('items')
            ->with()
            ->willReturn([
                'closure' => $closure = function (): void {
                },
                'method' => $methodName = 'items',
            ]);

        $behavior->expects($this->at(1))
            ->method('on')
            ->with('closure', $closure)
            ->willReturn(null);
        $behavior->expects($this->at(2))
            ->method('on')
            ->with('method', [$behavior, 'handle',])
            ->willReturn(null);

        $behavior->attach();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior::on()
     * @covers \Horat1us\Yii\StaticBehavior::off()
     */
    public function testStoringStaticEvents(): void
    {
        $event = new base\Event([
            'name' => 'some-event'
        ]);

        /** @var MockObject|StaticBehavior $behavior */
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['detach', 'on', 'off',])
            ->setMethods(['items',])
            ->getMock();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|StaticBehavior\ItemInterface $item */
        $item = $this->getMockBuilder(StaticBehavior\ItemInterface::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $item->expects($this->once())
            ->method('__invoke')
            ->with($event)
            ->willReturn(null);

        $behavior->target = base\Component::class;
        $behavior->expects($this->exactly(3))
            ->method('items')
            ->with()
            ->willReturn([
                $event->name => $item,
            ]);

        $behavior->attach();
        base\Event::trigger($behavior->target, $event->name, $event);

        $behavior->detach();
        base\Event::trigger($behavior->target, $event->name, $event);
    }
}
