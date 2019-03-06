<?php

declare(strict_types=1);

namespace Horat1us\Yii\StaticBehavior;

use Horat1us\Yii\StaticBehavior;
use yii\di;
use yii\base;

/**
 * Class Bootstrap
 * @package Horat1us\Yii\StaticBehavior
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /** @var array[]|string[]|StaticBehavior[] references */
    public $behaviors = [];

    public function init(): void
    {
        parent::init();

        $this->behaviors = array_map(function ($reference): StaticBehavior {
            /** @var StaticBehavior $behavior */
            $behavior = di\Instance::ensure($reference, StaticBehavior::class);
            return $behavior;
        }, $this->behaviors);
    }

    /**
     * @param base\Application $app
     */
    public function bootstrap($app): void
    {
        $app->on($app::EVENT_BEFORE_REQUEST, [$this, 'attach']);
        $app->on($app::EVENT_AFTER_REQUEST, [$this, 'detach']);
    }

    /**
     * @throws base\InvalidConfigException
     */
    public function attach(): void
    {
        foreach ($this->behaviors as $behavior) {
            $behavior->attach();
        }
    }

    public function detach(): void
    {
        foreach ($this->behaviors as $behavior) {
            $behavior->detach();
        }
    }
}
