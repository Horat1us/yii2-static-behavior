<?php

declare(strict_types=1);

namespace Horat1us\Yii;

use yii\di;
use yii\base;

/**
 * Class StaticBehavior
 * @package Horat1us\Yii
 */
class StaticBehavior extends base\BaseObject
{
    /** @var string class name to attach */
    public $target;

    public $items = [];

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!is_string($this->target)) {
            throw new base\InvalidConfigException(
                "Class name have to be specified as string, " . gettype($this->target) . " given.",
                1
            );
        }
        foreach ($this->items as $i => $reference) {
            if ($reference instanceof \Closure) {
                continue;
            }
            if (is_array($reference) && array_key_exists('class', $reference)
                || is_string($reference) && class_exists($reference)
                || $reference instanceof StaticBehavior\ItemInterface
            ) {
                continue;
            }
            throw new base\InvalidConfigException(
                "Invalid reference for item {$i}",
                2
            );
        }
    }

    public function items(): array
    {
        return $this->items;
    }

    /**
     * @throws base\InvalidConfigException
     */
    public function attach(): void
    {
        $items = $this->items();
        foreach ($items as $event => $reference) {
            if ($reference instanceof \Closure) {
                $this->on($event, $reference);
                continue;
            }

            $this->on($event, [$this, 'handle',]);
        }
    }

    public function detach(): void
    {
        $handlers = $this->items();
        foreach ($handlers as $event => $reference) {
            if ($reference instanceof \Closure) {
                $this->off($event, $reference);
                continue;
            }
            $this->off($event, [$this, 'handle',]);
        }
    }

    /**
     * @param base\Event $event
     * @throws base\InvalidConfigException
     * @throws \InvalidArgumentException if event name is not supported
     */
    public function handle(base\Event $event): void
    {
        $handlers = $this->items();
        if (!array_key_exists($event->name, $handlers)) {
            throw new \InvalidArgumentException(
                static::class . " can not handle {$this->target} {$event->name} event."
            );
        }

        $reference = $handlers[$event->name];

        /** @var \Closure|StaticBehavior\ItemInterface $handler */
        $handler = $reference instanceof \Closure
            ? $reference
            : di\Instance::ensure($reference, StaticBehavior\ItemInterface::class);

        $handler($event);
    }

    final protected function on(string $event, callable $handler): void
    {
        base\Event::on($this->target, $event, $handler);
    }

    final protected function off(string $event, callable $handler): void
    {
        base\Event::off($this->target, $event, $handler);
    }
}
