<?php

declare(strict_types=1);

namespace Horat1us\Yii\StaticBehavior;

use yii\base;

/**
 * Interface ItemInterface
 *
 * Describes simple event handler.
 *
 * @package Horat1us\Yii\StaticBehavior
 */
interface ItemInterface
{
    /**
     * @param base\Event $event
     */
    public function __invoke(base\Event $event): void;
}
