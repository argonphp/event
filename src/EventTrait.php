<?php declare(strict_types=1);

namespace Argon\Event;

trait EventTrait
{
    protected $eventListeners = [];

    private function isStatic(array $handler)
    {
        $method = new \ReflectionMethod($handler[0], $handler[1]);
        return $method->isStatic();
    }

    /**
     * Install an event listener for the event $eventName with the priority $priority.
     *
     * The handlers are sorted by their priority from lower to higher. Every number is acceptable as a priority even the
     * negative ones. If all the handlers have the same priority they are considered sorted and are evoked in the order
     * that they are defined. If an event handler returns **false** the chain of handlers for that event is stoped.
     *
     * The parameter **$eventHandler** is anything valid for the function [is_callable()](http://php.net/manual/en/function.is-callable.php).
     * There are four general forms for the **$eventHandler**
     * parameter:
     *
     * 1. **Calling a static method of a given class**
     *     ```php
     *     $eventManager->on('eventName', ['className', 'classMethod']);
     *     ```
     * 2. **Calling a public method of an instantiated class**
     *     ```php
     *     $instance = new SomeClass();
     *     $eventManager->on('eventName', [$instance, 'instanceMethodName']);
     *     ```
     * 3. **Using an anonymous function (closure)**
     *     ```php
     *     $eventManager->on('eventName', function() {
     *         // Some code...
     *     });
     *     ```
     * 4. **Using a string**
     * This is a variant of the first alternative, and causes the same result (calling a static method of a given class).
     *     ```php
     *     $eventManager->on('eventName', 'MyClass::myStaticMethod');
     *     ```
     * @param string $eventName
     * @param mixed  $eventHandler
     * @param int    $priority
     */
    public function on(string $eventName, callable $eventHandler, int $priority = 100) : callable
    {
        // Prevents calling a non-static method as if it was static!
        // It prevents errors and is a desireble behavior.
        if (is_array($eventHandler) and is_string($eventHandler[0]) and is_string($eventHandler[1]) and !$this->isStatic($eventHandler)) {
            throw new Exception\InvalidHandler;
        }

        // If it's the first listener, assume it's sorted!
        $this->eventListeners[$eventName]['sorted'] = ! isset($this->eventListeners[$eventName]);

        $this->eventListeners[$eventName]['handlers'][] = $eventHandler;
        $this->eventListeners[$eventName]['priority'][] = $priority;

        return $eventHandler;
    }
    
    
    public function listeners(string $eventName) : array
    {
        if (!isset($this->eventListeners[$eventName])) {
            return [];
        }

        // If all the listeners have the same priority, assume it's sorted!
        $this->eventListeners[$eventName]['sorted'] = (count(array_unique($this->eventListeners[$eventName]['priority'])) === 1);

        if (!$this->eventListeners[$eventName]['sorted']) {
            \array_multisort($this->eventListeners[$eventName]['priority'], SORT_ASC, $this->eventListeners[$eventName]['handlers']);
            $this->eventListeners[$eventName]['sorted'] = true;
        }
        return $this->eventListeners[$eventName]['handlers'];
    }


    /**
     * Trigger an event with the name $eventName and pass the array $argumentsForHandler for every event handler that is called.
     *
     * Example:
     * ```php
     * $eventManager = new EventManager();
     * $eventManager->on('hello', function() {
     *     echo "Hello ";
     * });
     * $eventManager->on('hello', function($name) {
     *     echo "$name!";
     * });
     * $eventManager->trigger('hello', ['World']);
     * ```
     * This will produce the output:
     *
     *     Hello World!
     *
     * @param string $eventName
     * @param array $argumentsForHandler
     */
    public function trigger(string $eventName, array $argumentsForHandler = []) : bool
    {
        foreach ($this->listeners($eventName) as $listener) {
            $return = call_user_func_array($listener, $argumentsForHandler);

            if ($return === false) {
                return false;
            }
        }

        return true;
    }

    public function off(string $eventName, callable $eventHandler = null) : bool
    {
        // Remove all the event listeners, and the declaration of the event itself
        if ($eventHandler === null and isset($this->eventListeners[$eventName])) {
            unset($this->eventListeners[$eventName]);
            return true;
        }

        foreach ($this->listeners($eventName) as $key => $listener) {
            if ($listener === $eventHandler) {
                unset($this->eventListeners[$eventName]['handlers'][$key]);
                unset($this->eventListeners[$eventName]['priority'][$key]);
                return true;
            }
        }

        return false;
    }

    public function one(string $eventName, callable $eventHandler, $priority = 100) : callable
    {

        $handler = function() use ($eventName, $eventHandler, &$handler) {
            $this->off($eventName, $handler);

            return \call_user_func_array($eventHandler, \func_get_args());
        };

        $this->on($eventName, $handler, $priority);

        return $eventHandler;

    }


}