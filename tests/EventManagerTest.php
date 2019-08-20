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
        // Set the error reporting to something similar used in production, 
        // it is necessary in order to test the method:
        //
        // testUsingANonStaticEventHandlerAsIfItWasStaticGeneratesAnException()
    	//
    	// In production enviroment, this warning will not show but PHP will not 
    	// stop you from calling a non-static method in a static context. 
    	// This is not allowed, and should be pointed out!
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        $this->event = new EventManager;
    }

    /**
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
     */
    public function testCanRegisterAnEventListenerAndFireAnEvent()
    {
        $this->expectOutputString('event');
                
        $this->event->on('event', function () {
            echo "event";
        });

        $this->event->trigger('event');
    }

    /**
     * @depends testCanRegisterAnEventListenerAndFireAnEvent
     * @covers Argon\Event\EventTrait::off
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
     */
    public function testCanRemoveAllListenersOfAnEvent()
    {
        $this->expectOutputString('');
        
        $this->event->off('event');

        $this->event->trigger('event');
    }

    /**
     * @covers Argon\Event\EventTrait::off
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
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
        $this->event->trigger('event');

        $this->event->off('event');
    }

    /**
     * @covers Argon\Event\EventTrait::off
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
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
        $this->event->trigger('event');

        $this->event->off('event');
    }

    // Used for testing with testUsingANonStaticEventHandlerAsIfItWasStaticGeneratesAnException()
    public function onEvent()
    {
    }

     /**
      * @covers Argon\Event\EventTrait::on
      * @covers Argon\Event\Exception\InvalidHandler
      * @covers Argon\Event\EventTrait::isStatic
      */
     public function testUsingANonStaticEventHandlerAsIfItWasStaticGeneratesAnException()
     {
         $this->expectException(InvalidHandler::class);
         $this->event->on('event', [__CLASS__, 'onEvent']);
     }


    /**
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\Exception\InvalidHandler
     */
    public function testUsingANonCallableHandlerGeneratesAnException()
    {
        $this->expectException(TypeError::class);
        $this->event->on('event', 'abc');
    }

    /**
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
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
        $this->event->trigger('event');
    }

    /**
     * @covers Argon\Event\EventTrait::on
     * @covers Argon\Event\EventTrait::one
     * @covers Argon\Event\EventTrait::off
     * @covers Argon\Event\EventTrait::trigger
     * @covers Argon\Event\EventTrait::listeners
     */
    public function testEventHandlerRegisteredWithOneMethodRunsOnlyOnce()
    {
        $this->expectOutputString('ABB');
        $this->event->one('event', function() {
            echo 'A';
        });
        $this->event->on('event', function() {
            echo 'B';
        });
        $this->event->trigger('event');
        $this->event->trigger('event');
    }
}
