<?php
/**
 * Contains EventDispatcherInterface Interface.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

/**
 * Interface EventDispatcherInterface
 * The EventDispatcherInterface is the central point of Yapeal's event
 * listener system. Listeners are registered on the manager and events are
 * dispatched through the manager.
 *
 * @api
 */
interface EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * Each combination of $eventName, $listener, and $priority is unique.
     * Re-adding the same listener will cause it to leave the $eventName,
     * $priority chain and be added to the end of that chain.
     * Example:
     *     $listeners[$eventName][$priority] = ['listener1', 'listener2'];
     * Re-add 'listener1' and it becomes:
     *     $listeners[$eventName][$priority] = ['listener2', 'listener1'];
     *
     * @param string $eventName   The event to listen for
     * @param array  $callback    The listener to be added. Needs to have either
     *                            [object, 'methodName'] or
     *                            ['className', 'methodName']
     * @param int    $priority    The higher this value, the earlier an event
     *                            listener will be triggered in the chain
     *                            (defaults to 0)
     *
     * @return self
     * @api
     */
    public function addListener($eventName, array $callback, $priority = 0);
    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events he is
     * interested in and added as a listener for these events.
     *
     * @param string|object $class The subscriber class instance or name.
     *
     * @return self
     * @api
     */
    public function addSubscriber($class);
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string         $eventName The name of the event to dispatch. The
     *                                  name of the event is the name of the
     *                                  method that is invoked on listeners.
     * @param EventInterface $event     The event to pass to the event
     *                                  handlers/listeners. If not supplied, an
     *                                  empty Event instance is created.
     *
     * @return EventInterface
     *
     * @api
     */
    public function dispatch($eventName, EventInterface $event = null);
    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return array The event listeners for the specified event, or all event
     *               listeners by event name
     */
    public function getListeners($eventName = null);
    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return bool true if the specified event has any listeners, false
     *              otherwise
     */
    public function hasListeners($eventName = null);
    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName The event to remove a listener from
     * @param array    $callback  The listener to be removed. Needs to have
     *                            either [object, 'methodName'] or
     *                            ['className', 'methodName']
     * @param int|null $priority  Priority level of listener to be removed.
     *                            If it is the default value null then listener
     *                            will be removed for all found priorities.
     *
     * @return self
     * @api
     */
    public function removeListener(
        $eventName,
        array $callback,
        $priority = null
    );
    /**
     * Removes an event subscriber.
     *
     * @param string|object $class The subscriber class instance or name.
     *
     * @return self
     * @api
     */
    public function removeSubscriber($class);
}
