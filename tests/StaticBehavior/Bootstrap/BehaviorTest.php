<?php

declare(strict_types=1);

namespace Horat1us\Yii\Tests\StaticBehavior\Bootstrap;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Horat1us\Yii\StaticBehavior;
use yii\base;

/**
 * Class BehaviorTest
 * @package Horat1us\Yii\Tests\StaticBehavior\Bootstrap
 */
class BehaviorTest extends TestCase
{
    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::init()
     */
    public function testInvalidConfiguration(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        new StaticBehavior\Bootstrap\Behavior([
            'behaviors' => [
                new \stdClass(),
            ],
        ]);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::init()
     */
    public function testInvalidEventsConfiguration(): void
    {
        $this->expectExceptionMessage('Invalid handler for afterAction: only beforeAction and afterAction supported');
        $this->expectExceptionCode(1);
        $this->expectException(base\InvalidConfigException::class);

        new StaticBehavior\Bootstrap\Behavior([
            'behaviors' => [],
            'events' => [
                base\Module::EVENT_AFTER_ACTION => 'invalid-method',
            ],
        ]);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::init()
     */
    public function testInstantiating(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $staticBehavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->getMock();

        $behavior = new StaticBehavior\Bootstrap\Behavior([
            'behaviors' => [
                'mock' => get_class($staticBehavior),
            ],
        ]);

        $this->assertInstanceOf(
            get_class($staticBehavior),
            $behavior->behaviors['mock']
        );
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::beforeAction()
     */
    public function testAttachingBeforeAction(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $firstBehavior = $this->createMock(StaticBehavior::class);
        $firstBehavior->expects($this->once())
            ->method('attach');

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondBehavior = $this->createMock(StaticBehavior::class);
        $secondBehavior->expects($this->once())
            ->method('attach');

        $behavior = new StaticBehavior\Bootstrap\Behavior([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $behavior->beforeAction();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::afterAction()
     */
    public function testDetachingAfterAction(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $firstBehavior = $this->createMock(StaticBehavior::class);
        $firstBehavior->expects($this->once())
            ->method('detach');

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondBehavior = $this->createMock(StaticBehavior::class);
        $secondBehavior->expects($this->once())
            ->method('detach');

        $behavior = new StaticBehavior\Bootstrap\Behavior([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $behavior->afterAction();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::events()
     */
    public function testAttach(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|StaticBehavior\Bootstrap\Behavior $behavior */
        $behavior = $this->getMockBuilder(StaticBehavior\Bootstrap\Behavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['attach', 'events',])
            ->getMock();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|base\Component $owner */
        $owner = $this->createMock(base\Component::class);
        $owner->expects($this->at(0))
            ->method('on')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$behavior, 'beforeAction']);
        $owner->expects($this->at(1))
            ->method('on')
            ->with(base\Module::EVENT_AFTER_ACTION, [$behavior, 'afterAction']);

        $behavior->attach($owner);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::events()
     */
    public function testDetach(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|StaticBehavior\Bootstrap\Behavior $behavior */
        $behavior = $this->getMockBuilder(StaticBehavior\Bootstrap\Behavior::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['attach', 'events',])
            ->setMethods(['off',])
            ->getMock();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|base\Component $owner */
        $owner = $this->createMock(base\Component::class);

        $owner->expects($this->at(0))
            ->method('on')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$behavior, 'beforeAction']);
        $owner->expects($this->at(1))
            ->method('on')
            ->with(base\Module::EVENT_AFTER_ACTION, [$behavior, 'afterAction']);

        $owner->expects($this->at(2))
            ->method('off')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$behavior, 'beforeAction']);
        $owner->expects($this->at(3))
            ->method('off')
            ->with(base\Module::EVENT_AFTER_ACTION, [$behavior, 'afterAction']);

        $behavior->attach($owner);
        $behavior->detach();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Behavior::events()
     */
    public function testConfigurableEvents(): void
    {
        $behavior = new StaticBehavior\Bootstrap\Behavior();
        $events = [
            'CUSTOM_AFTER_ACTION' => 'afterAction',
            'CUSTOM_BEFORE_ACTION' => 'beforeAction',
        ];
        $behavior->events = $events;

        $this->assertEquals(
            $events,
            $behavior->events()
        );
    }
}
