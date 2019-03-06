<?php

declare(strict_types=1);

namespace Horat1us\Yii\StaticBehavior\Bootstrap;

use Horat1us\Yii\StaticBehavior;
use yii\base;
use yii\di;

/**
 * Class Behavior
 * @package Horat1us\Yii\StaticBehavior\Bootstrap
 */
class Behavior extends base\Behavior
{
    /** @var array[]|string[]|StaticBehavior[] references */
    public $behaviors = [];

    /** @var string[] */
    public $events = [
        base\Module::EVENT_BEFORE_ACTION => 'beforeAction',
        base\Module::EVENT_AFTER_ACTION => 'afterAction',
    ];

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->behaviors = array_map(function ($reference): StaticBehavior {
            /** @var StaticBehavior $behavior */
            $behavior = di\Instance::ensure($reference, StaticBehavior::class);
            return $behavior;
        }, $this->behaviors);

        foreach ($this->events as $action => $handler) {
            if (!is_string($handler) || !method_exists($this, $handler)) {
                throw new base\InvalidConfigException(
                    "Invalid handler for {$action}: only beforeAction and afterAction supported.",
                    1
                );
            }
        }
    }

    public function events(): array
    {
        return $this->events;
    }

    /**
     * Attach static behaviors
     *
     * @throws base\InvalidConfigException
     */
    public function beforeAction(): void
    {
        foreach ($this->behaviors as $behavior) {
            $behavior->attach();
        }
    }

    /**
     * Clean-up attached static behavior
     */
    public function afterAction(): void
    {
        foreach ($this->behaviors as $behavior) {
            $behavior->detach();
        }
    }
}
