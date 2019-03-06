<?php

namespace Example;

use Horat1us\Yii\StaticBehavior;
use yii\base;
use yii\web;

// Delivery service to send message to user (recipient)
interface Delivery {
    public function send(string $recipient, string $message);
}

// Token entity
interface Token {
    public function getValue(): string;
    public function getOwner(): string;
}

/**
 * Class ActionLogin
 * @package Example
 */
class ActionLogin extends base\Action
{
    public const EVENT_TOKEN = 'token';

    public function run() {
        // Create token here
        /** @var Token $token */

        $this->trigger(static::EVENT_TOKEN, new base\Event([
            'data' => $token,
        ]));

        return ['state' => 'ok'];
    }
}

/**
 * Class AuthenticationController
 * @package Example
 */
class AuthenticationController extends web\Controller
{
    public function actions(): array
    {
        return [
            'login' => ActionLogin::class,
        ];
    }
}

/**
 * Class TokenItem
 * @package Example
 */
class TokenItem extends StaticBehavior\Item
{
    /** @var Delivery  */
    protected $delivery;

    public function __construct(Delivery $delivery, array $config = [])
    {
        parent::__construct($config);
        $this->delivery = $delivery;
    }

    public function handlers(): array {
        return [
            ActionLogin::EVENT_TOKEN => 'handleTokenEvent',
        ];
    }

    /**
     * @param base\Event $event
     * @throws \InvalidArgumentException
     */
    public function handleTokenEvent(base\Event $event): void
    {
        if(!$event->data instanceof Token) {
            throw new \InvalidArgumentException("Cannot handle event without token data.");
        }
        $token = $event->data;
        $this->delivery->send(
            $recipient = $token->getOwner(),
            $message = $token->getValue()
        );
    }
}

class Module extends base\Module {
    public $controllerMap = [
        'class' => AuthenticationController::class,
        'as staticLogin' => [
            'class' => StaticBehavior\Bootstrap\Behavior::class,
            'events' => [
                // handlers will be attached only before controller run
                base\Controller::EVENT_BEFORE_ACTION => 'beforeAction',
                base\Controller::EVENT_AFTER_ACTION => 'afterAction',
            ],
            'behaviors' => [
                [
                    'class' => StaticBehavior::class,
                    'target' => ActionLogin::class,
                    'items' => [
                        'sendToken' => TokenItem::class,
                    ],
                ],
            ],
        ],
    ];
}

// append module to your application
