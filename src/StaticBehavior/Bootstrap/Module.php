<?php

declare(strict_types=1);

namespace Horat1us\Yii\StaticBehavior\Bootstrap;

use Horat1us\Yii\StaticBehavior;
use yii\base;
use yii\di;

/**
 * Class Module
 * @package Horat1us\Yii\StaticBehavior\Bootstrap
 */
class Module extends base\Behavior
{
    /** @var array[]|string[]|StaticBehavior[] references */
    public $behaviors = [];

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
    }

    public function events(): array
    {
        return [
            base\Module::EVENT_BEFORE_ACTION => 'beforeAction',
            base\Module::EVENT_AFTER_ACTION => 'afterAction',
        ];
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
