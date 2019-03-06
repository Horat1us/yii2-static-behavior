<?php

namespace Example;

use Horat1us\Yii\StaticBehavior;
use yii\console;
use yii\base;
use yii\db;
use yii\di;

// region External classes to be configured

/**
 * Class User
 * @package Example
 */
class User extends db\ActiveRecord
{
}

// endregion

// region Custom event handlers

/**
 * Class CustomItem
 * @package Example
 */
class CustomItem implements StaticBehavior\ItemInterface
{

    public function __invoke(base\Event $event): void
    {
        if ($event->name === db\ActiveRecord::EVENT_INIT) {
            // handle specific event
            echo "init " . get_class($event->sender);
        } else {
            // handle any event
            echo "{$event->name} " . get_class($event->sender);
        }
    }
}

/**
 * Class MultipleItem
 * @package Example
 */
class MultipleItem extends StaticBehavior\Item
{
    /** @var string|array|db\Connection */
    public $db = 'db';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->db = di\Instance::ensure($this->db, db\Connection::class);
    }

    public function handlers(): array
    {
        return [
            // you can specify method name
            db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            db\ActiveRecord::EVENT_BEFORE_INSERT => [$this, 'beforeInsert'],
            db\ActiveRecord::EVENT_AFTER_UPDATE => function (db\AfterSaveEvent $event) {
                // handle event here
            },
        ];
    }

    public function afterInsert(db\AfterSaveEvent $event): void
    {
        // handle after save event
    }

    public function beforeInsert(base\Event $event): void
    {
        // handle before update event
    }
}
// endregion

// region Application configuration

/** @noinspection PhpUnhandledExceptionInspection */
$application = new console\Application([
    'bootstrap' => [
        // ... another bootstraps
        'static-behaviors' => [
            'class' => StaticBehavior\Bootstrap::class,
            'behaviors' => [
                'class' => StaticBehavior::class,
                'target' => User::class,
                'items' => [
                    db\ActiveRecord::EVENT_INIT => CustomItem::class,
                    db\ActiveRecord::EVENT_AFTER_INSERT => MultipleItem::class,
                    db\ActiveRecord::EVENT_BEFORE_INSERT => MultipleItem::class,
                    db\ActiveRecord::EVENT_AFTER_UPDATE => MultipleItem::class,
                    db\ActiveRecord::EVENT_BEFORE_UPDATE => function (base\Event $event) {
                        // handle event here
                    },
                ],
            ],
        ],
    ],
]);

$application->run();
// endregion
