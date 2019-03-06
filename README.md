# Yii2 Static Behavior
[![Build Status](https://travis-ci.org/Horat1us/yii2-static-behavior.svg?branch=master)](https://travis-ci.org/Horat1us/yii2-static-behavior)
[![codecov](https://codecov.io/gh/Horat1us/yii2-static-behavior/branch/master/graph/badge.svg)](https://codecov.io/gh/Horat1us/yii2-static-behavior)

This package implements static behavior to allow you append behavior-like
event handlers to external objects.

Usage example:
You use package that contains ActiveRecord. If you need to handle some
events of this record (beforeInsert, afterInsert etc.), this package definitely
for you.

It uses static `yii\base\Event::on()` and `yii\base\Event::off()` under the hood.

The main advantage of using this static behavior:
- event handler class (**ItemInterface**) will be instantiated before event will be called,
so dependencies will be lazy-loaded too.

## Installation
```bash
composer require horat1us/yii2-static-behavior
```

## Structure
- [StaticBehavior](./src/StaticBehavior.php) - main class that deals with attaching
and detaching handlers.
- [Bootstrap](./src/StaticBehavior/Bootstrap.php) - configurable application
bootstrap class. It use **StaticBehavior** to attach handlers before application
request and detach after request.
- [ItemInterface](./src/StaticBehavior/ItemInterface.php) - describes simple event handler
(to be used in **StaticBehavior**).
- [Item](./src/StaticBehavior/Item.php) - abstract **ItemInterface** implementation.
Allows to configure handlers (for example methods) for different events.
- [Bootstrap\Behavior](src/StaticBehavior/Bootstrap/Behavior.php) - behavior to attach handlers
before action and detach after controller/module action. Should be used if some handlers will be used only when
module executed.

## Usage

### StaticBehavior
First, you need to implement [ItemInterface](./src/StaticBehavior/ItemInterface.php).
Then, configure your **StaticBehavior** with implemented items.

*Note: you can use \Closure instead of item classes (not recommended)*

```php
<?php

namespace Example;

use yii\db;
use yii\base;
use Horat1us\Yii\StaticBehavior;

class ExternalRecord extends db\ActiveRecord
{
    // some implementation you cannot change
}

$staticBehavior = new StaticBehavior([
    'target' => ExternalRecord::class,
    'items' => [
        base\Model::EVENT_BEFORE_VALIDATE => function(base\Event $event) {
            // you can handle event in \Closure
        },
        base\Model::EVENT_AFTER_VALIDATE => [
            // custom item implementation
            'class' => StaticBehavior\Item::class,
        ],
        db\ActiveRecord::EVENT_INIT => [
            // or use interface to implement items
            'class' => StaticBehavior\ItemInterface::class,
        ],
    ],
]);

// To attach event handlers to class
$staticBehavior->attach();

// To detach attached event handlers from class
$staticBehavior->detach();
```

[Detailed example](./examples/static-behavior.php)

### Bootstrap

To bootstrap your application you should use [Bootstrap](./src/StaticBehavior/Bootstrap.php).
It will attach handlers before application request and detach it after request.

```php
<?php

use Horat1us\Yii\StaticBehavior;

// config.php

return [
    'bootstrap' => [
        'class' => StaticBehavior\Bootstrap::class,
        'behaviors' => [
            // StaticBehavior references. See previous section for examples.
        ],
    ],
];
```

### Item
Main purpose of items - lazy dependency injection for event handlers.
You can define dependencies in constructor or use yii2-way configuration (prefered).

Item that handles `yii\db\ActiveRecord` events:
[Example (handle `yii\db\ActiveRecord` events)](./examples/item.php)

### Bootstrap\Behavior
This behavior should be used when some handlers will be used only in one module.
For example:
- sending messages after authentication
- sending message with token for two-factor authentication

It can be used with controller or any another component (you will need to configure events).

[Example (authorization tokens)](./examples/bootstrap-behavior.php)

## Contributors
- [Alexander <Horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
