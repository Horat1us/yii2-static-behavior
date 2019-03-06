<?php

declare(strict_types=1);

namespace Horat1us\Yii\StaticBehavior;

use yii\base;

/**
 * Class Item
 * @package Horat1us\Yii\StaticBehavior
 */
abstract class Item extends base\BaseObject implements ItemInterface
{
    /**
     * Key should be event name and value should be handler on of type:
     *  - \Closure
     *  - string with method name
     *  - callable array
     *
     * @return array
     */
    abstract public function handlers(): array;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $handlers = $this->handlers();
        foreach ($handlers as $event => $item) {
            if (is_string($item) && method_exists($this, $item)) {
                continue;
            }

            if ($item instanceof \Closure || is_array($item) && is_callable($item)) {
                continue;
            }

            throw new base\InvalidConfigException(
                "Invalid handler configured for {$event} event.",
                2
            );
        }
    }

    /**
     * @param base\Event $event
     * @throws \InvalidArgumentException if event name is not supported
     */
    final public function __invoke(base\Event $event): void
    {
        $handlers = $this->handlers();
        if (!array_key_exists($event->name, $handlers)) {
            throw new \InvalidArgumentException(
                static::class . " can not handle " . $event->name,
                1
            );
        }

        $handler = $handlers[$event->name];
        if (is_string($handler)) {
            $handler = [$this, $handler];
        }

        call_user_func($handler, $event);
    }
}
