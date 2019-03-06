<?php

declare(strict_types=1);

namespace Horat1us\Yii\Tests\StaticBehavior\Bootstrap;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Horat1us\Yii\StaticBehavior;
use yii\base;

/**
 * Class ModuleTest
 * @package Horat1us\Yii\Tests\StaticBehavior\Bootstrap
 */
class ModuleTest extends TestCase
{
    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::init()
     */
    public function testInvalidConfiguration(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        new StaticBehavior\Bootstrap\Module([
            'behaviors' => [
                new \stdClass(),
            ],
        ]);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::init()
     */
    public function testInstantiating(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $behavior = $this->getMockBuilder(StaticBehavior::class)
            ->disableOriginalConstructor()
            ->getMock();

        $bootstrap = new StaticBehavior\Bootstrap\Module([
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
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::beforeAction()
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

        $bootstrap = new StaticBehavior\Bootstrap\Module([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $bootstrap->beforeAction();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::afterAction()
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

        $bootstrap = new StaticBehavior\Bootstrap\Module([
            'behaviors' => [
                $firstBehavior,
                $secondBehavior,
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection behavior mocked */
        $bootstrap->afterAction();
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::events()
     */
    public function testAttach(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|StaticBehavior\Bootstrap\Module $bootstrap */
        $bootstrap = $this->getMockBuilder(StaticBehavior\Bootstrap\Module::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['attach', 'events',])
            ->getMock();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|base\Component $owner */
        $owner = $this->createMock(base\Component::class);
        $owner->expects($this->at(0))
            ->method('on')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$bootstrap, 'beforeAction']);
        $owner->expects($this->at(1))
            ->method('on')
            ->with(base\Module::EVENT_AFTER_ACTION, [$bootstrap, 'afterAction']);

        $bootstrap->attach($owner);
    }

    /**
     * @covers \Horat1us\Yii\StaticBehavior\Bootstrap\Module::events()
     */
    public function testDetach(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|StaticBehavior\Bootstrap\Module $bootstrap */
        $bootstrap = $this->getMockBuilder(StaticBehavior\Bootstrap\Module::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['attach', 'events',])
            ->setMethods(['off',])
            ->getMock();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MockObject|base\Component $owner */
        $owner = $this->createMock(base\Component::class);

        $owner->expects($this->at(0))
            ->method('on')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$bootstrap, 'beforeAction']);
        $owner->expects($this->at(1))
            ->method('on')
            ->with(base\Module::EVENT_AFTER_ACTION, [$bootstrap, 'afterAction']);

        $owner->expects($this->at(2))
            ->method('off')
            ->with(base\Module::EVENT_BEFORE_ACTION, [$bootstrap, 'beforeAction']);
        $owner->expects($this->at(3))
            ->method('off')
            ->with(base\Module::EVENT_AFTER_ACTION, [$bootstrap, 'afterAction']);

        $bootstrap->attach($owner);
        $bootstrap->detach();
    }
}
