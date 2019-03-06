<?php declare(strict_types=1);

namespace Argon\Event;

trait EventTrait
{
    protected $listeners = [];

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
    public function on(string $eventName, $eventHandler, int $priority = 100)
    {
        if (!is_callable($eventHandler)) {
            throw new Exception\InvalidHandler;
        }
        // Prevents calling a non-static method as if it was static!
        // It prevents errors and is a desireble behavior.
        if (is_array($eventHandler) and is_string($eventHandler[0]) and is_string($eventHandler[1]) and !$this->isStatic($eventHandler)) {
            throw new Exception\InvalidHandler;
        }

        if (!isset($this->listeners[$eventName])) { // It's the first listener, so assume it's sorted!
            $this->listeners[$eventName]['sorted'] = true;
        } else {
            $this->listeners[$eventName]['sorted'] = false;
        }

        $this->listeners[$eventName]['handlers'][] = $eventHandler;
        $this->listeners[$eventName]['priority'][] = $priority;
    }
    

    protected function getListeners(string $eventName) : array
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        // If all the listeners have the same priority assume it is sorted!
        if (count(array_unique($this->listeners[$eventName]['priority'])) === 1) {
            $this->listeners[$eventName]['sorted'] = true;
        } else {
            $this->listeners[$eventName]['sorted'] = false;
        }

        if (!$this->listeners[$eventName]['sorted']) {
            \array_multisort($this->listeners[$eventName]['priority'], SORT_ASC, $this->listeners[$eventName]['handlers']);
            $this->listeners[$eventName]['sorted'] = true;
        }
        return $this->listeners[$eventName]['handlers'];
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
     * $eventManager->fire('hello', ['World']);
     * ```
     * This will produce the output: 
     *     
     *     Hello World!
     *
     * @param string $eventName
     * @param array $argumentsForHandler
     */ 
    public function fire(string $eventName, array $argumentsForHandler = [])
    {
        foreach ($this->getListeners($eventName) as $listener) {
            $return = call_user_func_array($listener, $argumentsForHandler);

            if ($return === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes all event listeners of the event **$eventName**.
     * @param string $eventName
     */
    public function removeAllListeners(string $eventName)
    {
        if (isset($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }
}
