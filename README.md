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

The most basic way of usin *Argon/Event* is to create a new instance of **EventManger** and using it. The code bellow shows how it can be done:

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

## API

### EventManager::on( string $eventName, mixed $eventHandler, int $priority = 100)

Install an event listener for the event **$eventName** with the priority **$priority**.

The handlers are sorted by their priority from lower to higher. Every number is acceptable as a priority even the negative ones. If all the handlers have the same priority they are considered sorted and are evoked in the order that they are defined. If an event handler returns **false** the chain of handlers for that event is stoped.

The parameter **$eventHandler** is anything valid for the function [is_callable()](http://php.net/manual/en/function.is-callable.php). There are four general forms for the **$eventHandler** parameter:

1. **Calling a static method of a given class**
    ```php
    $eventManager->on('eventName', ['className', 'classMethod']);
    ```
2. **Calling a public method of an instantiated class**
    ```php
    $instance = new SomeClass();
    $eventManager->on('eventName', [$instance, 'instanceMethodName']);
    ```
3. **Using an anonymous function (closure)**
    ```php
    $eventManager->on('eventName', function() {
        // Some code...
    });
    ```
4. **Using a string**
This is a variant of the first alternative, and causes the same result, calling a static method of a given class.
    ```php
    $eventManager->on('eventName', 'MyClass::myStaticMethod');
    ```
### EventManager::fire(string $eventName, array $argumentsForHandler = [])

Trigger an event with the name **$eventName** and pass the array **$argumentsForHandler** for every event handler that is called.

Example:
```php
$eventManager = new EventManager();
$eventManager->on('hello', function() {
    echo "Hello ";
});
$eventManager->on('hello', function($name) {
    echo "$name!";
});
$eventManager->fire('hello', ['World']);
```
This will produce the output: 
    
    Hello World!

### EventManager::removeAllListeners(string $eventName)

Removes all event listeners of the event **$eventName**.