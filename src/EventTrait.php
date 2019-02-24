<?php declare(strict_types=1);

namespace Argon\Event;

trait EventTrait {

    protected $listeners = [];

    public function on(string $eventName, $eventHandler, int $priority = 100)
    {
        if (!is_callable($eventHandler)) {
            throw new Exception\InvalidHandler;
        } else {
            // Prevents calling a non-static method as if it was static!
            // It prevents errors and is a desireble behavior.
            if (is_array($eventHandler) and is_string($eventHandler[0]) and is_string($eventHandler[1])) {
                $method = new \ReflectionMethod($eventHandler[0], $eventHandler[1]);
                if (!$method->isStatic()) {
                    throw new Exception\InvalidHandler;
                }
            }
        }
        if (!isset($this->listeners[$eventName])) { // It's the first listener, so assume it's sorted!
            $this->listeners[$eventName] = [
                'sorted' => true,
                'handlers' => [$eventHandler],
                'priority' => [$priority]
            ];
        } else {
            $this->listeners[$eventName]['handlers'][] = $eventHandler;
            $this->listeners[$eventName]['priority'][] = $priority;
            $this->listeners[$eventName]['sorted'] = false;
        }
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

    public function removeAllListeners(string $eventName)
    {
        if (isset($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

}