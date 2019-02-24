<?php declare(strict_types=1);

require "vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use Argon\Event\Exception\InvalidHandler;
use Argon\Event\EventManager;

final class EventManagerTest extends TestCase
{

    protected $event;

    public function setUp() : void
    {
        $this->event = new EventManager;
    }

    /**
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\EventManager::fire
     * @covers Argon\Event\EventManager::getListeners
     */
    public function testCanRegisterAnEventListenerAndFireAnEvent()
    {
        $this->expectOutputString('event');
                
        $this->event->on('event', function () {
            echo "event";
        });

        $this->event->fire('event');
    }

    /**
     * @depends testCanRegisterAnEventListenerAndFireAnEvent
     * @covers Argon\Event\EventManager::removeAllListeners
     * @covers Argon\Event\EventManager::fire
     * @covers Argon\Event\EventManager::getListeners
     */
    public function testCanRemoveAllListenersOfAnEvent()
    {
        $this->expectOutputString('');
        
        $this->event->removeAllListeners('event');

        $this->event->fire('event');
    }

    /**
     * @covers Argon\Event\EventManager::removeAllListeners
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\EventManager::fire
     * @covers Argon\Event\EventManager::getListeners
     */
    public function testListenersOfSamePriorityAreCalledInTheOrderTheyAreDefined()
    {
        $this->event->on('event', function () {
            echo "This ";
        }, 20);
        $this->event->on('event', function () {
            echo "is a ";
        }, 20);
        $this->event->on('event', function () {
            echo "event handler!";
        }, 20);
        
        $this->expectOutputString('This is a event handler!');
        $this->event->fire('event');

        $this->event->removeAllListeners('event');
    }

    /**
     * @covers Argon\Event\EventManager::removeAllListeners
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\EventManager::fire
     * @covers Argon\Event\EventManager::getListeners
     */
    public function testListenersOfDifferentPriorityAreSortedByTheirPriorityFromLowToHigh()
    {
        $this->event->on('event', function () {
            echo "is a ";
        }, 20);
        $this->event->on('event', function () {
            echo "This ";
        }, 10);
        $this->event->on('event', function () {
            echo "event handler!";
        }, 30);
        
        $this->expectOutputString('This is a event handler!');
        $this->event->fire('event');

        $this->event->removeAllListeners('event');
    }

    // Used for testing with testUsingANonStaticEventHandlerAsIfItWasStaticGeneratesAnException()
    public function onEvent()
    {
    }

    /**
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\Exception\InvalidHandler
     */
    public function testUsingANonStaticEventHandlerAsIfItWasStaticGeneratesAnException()
    {
        $this->expectException(InvalidHandler::class);
        $this->event->on('event', [__CLASS__, 'onEvent']);
    }


    /**
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\Exception\InvalidHandler
     */
    public function testUsingANonCallableHandlerGeneratesAnException()
    {
        $this->expectException(InvalidHandler::class);
        $this->event->on('event', 'abc');
    }

    /**
     * @covers Argon\Event\EventManager::on
     * @covers Argon\Event\EventManager::fire
     * @covers Argon\Event\EventManager::getListeners
     */
    public function testReturningFalseFromAnEventHandlerStopsTheExecutionOfOtherListeners()
    {
        $this->expectOutputString('A');
        $this->event->on('event', function() {
            echo 'A';
            return false;
        });
        $this->event->on('event', function() {
            echo 'B';
        });
        $this->event->fire('event');
    }
}
