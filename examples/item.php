<?php

namespace Example;

use Horat1us\Yii\StaticBehavior;
use yii\base;
use yii\web;
use yii\db;
use yii\di;

/**
 * Class Item
 * @package Example
 */
class Item extends StaticBehavior\Item
{
    /**
     * Configurable dependency
     * @var string|array|db\Connection reference
     */
    public $db;

    /**
     * Constructor dependency
     * @var web\Request
     */
    protected  $request;

    public function init(): void
    {
        parent::init();
        // Ensuring dependencies
        $this->db = di\Instance::ensure($this->db, db\Connection::class);
    }

    public function handlers(): array
    {
        return [
            // using method handler defined as string with name
            db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            // using method handler defined as array callable
            db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            // using \Closure handler
            db\ActiveRecord::EVENT_INIT => function(base\Event $event): void {
                // handle event
            },
        ];
    }

    protected function beforeInsert(base\Event $event): void
    {
        // handle event
    }

    protected function afterInsert(db\AfterSaveEvent $event): void
    {
        // handle evnet
    }
}
