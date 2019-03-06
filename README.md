# Argon/Event
Library for event-based development in PHP.

[![Build Status](https://travis-ci.org/argonphp/event.svg?branch=master)](https://travis-ci.org/argonphp/event)
[![Code Climate coverage](https://img.shields.io/codeclimate/coverage/argonphp/event.svg)](https://codeclimate.com/github/argonphp/event)
[![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/argonphp/event.svg)](https://codeclimate.com/github/argonphp/event)
[![Code Style PSR-1](https://img.shields.io/badge/code_style-PSR--1-brightgreen.svg)](https://www.php-fig.org/psr/psr-1/)
[![Code Style PSR-2](https://img.shields.io/badge/code_style-PSR--2-brightgreen.svg)](https://www.php-fig.org/psr/psr-2/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://choosealicense.com/licenses/mit/)

## How to install

The package can be easly installed using composer, with the following command:

    composer require argon/event:dev-master

## Getting started (basic)

The most basic way of using *Argon/Event* is to create a new instance of **EventManger** and using it. The code bellow shows how it can be done:

```php
<?php

require "vendor/autoload.php";

use Argon\Event\EventManager;

$eventManager = new EventManager();

$eventManager->on('eventName', function() {
    echo "This message was triggered by eventName!";
});

$eventManager->fire('eventName');
```

## Getting started (advanced)

An most advanced way of using *Argon/Event* is using the **EventTrait** and incorporate the functionality inside your own class. The code bellow will gives you some ideas:

```php
<?php

require "vendor/autoload.php";

use Argon\Event\EventTrait;

final class MyEventManager
{
    use EventTrait;
    
    public function myOwnNewMethod()
    {
        echo "Hello World!";
    }
}

$myEventManager = new MyEventManager();

$myEventManager->on('eventName', function() {
    echo "This message was triggered by eventName!";
});

$myEventManager->fire('eventName');

```

As you can see, using the **EventTrait** is possible to incorporate all the behavior of the EventManager inside your own class.
