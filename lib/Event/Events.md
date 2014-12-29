Events.md
=========

Below are some of the details about the Yapeal event system and how it can be
used.

## Background

First have a look at
[Event-driven programming](http://en.wikipedia.org/wiki/Event-driven_programming)
if you aren't sure what events are and how they are commonly used in
programming. Yapeal's event system is a thin wrapper around the Symfony
[EventDispatcher Component](http://symfony.com/doc/current/components/event_dispatcher/introduction.html).
I'll assume in the rest of this text that you've read how the component works
and only explain the details of what Yapeal has added to it.

## Classes

Below I'll do a brief overview of the classes and interfaces used in Yapeal.

### EveApiEvent

`EveApiEvent` is extended from the base `Event` class and basically acts as an
event wrapper around `EveAPiXmlData` which you can access using `getData()` and
`setData()`. This class implements the `EveApiEventInterface`. You will NOT
need to create instances of this class directly but will be receive them from
Yapeal instead.

### EveApiEventInterface

This is a simple interface that extends from the `EventInterface` and allow for
future transparent changes to the underlying classes. It defines the `getData()`
and `setData()` required methods.

### EventInterface

Interface create to help isolate Yapeal from changes to the Symfony
EventDispatcher Component and allow possibility to replace it with something
else in the future if needed.

### YapealEventDispatcher

This class extends from the Symfony `EventDispatcher` class and implements the
`YapealEventDispatcherInterface`. It add the `dispatchEveApiEvent()` which
returns a `EveApiEvent` object vs the default `dispatch()` method that return a
generic `Event` object instead.

### YapealEventDispatcherInterface

Interface that extends Symfony's `EventDispatcherInterface` and adds
`dispatchEveApiEvent()` method.

## Eve API Event List

Currently (Dec 2014) the only events that Yapeal emits are Eve API related ones
having to do with the varies stages of the processing but it is possible that
other events maybe added in the future. The following is a list of the Eve API
events that are currently emitted when Yapeal is ran.

- eve_api.start - Occurs when per Eve API class is first called but before doing
    anything. Can be used for once per Eve API initializing etc.
- eve_api.pre_retrieve - Different from above start event for account, char, and
    corp sections where it occurs on each key, char, corp, etc combo.
- eve_api.pre_transform - Occurs once the raw XML data is actually retrieved but
    before any xslt or other transforms have been applied. Can also be used as
    `eve_api.post_retrieve`.
- eve_api.pre_validate - Occurs after things like xslt transforms have changed
    the raw XML but before the transformed XML has been validated using XSD etc.
    Can also be used as `eve_api.post_transform`.
- eve_api.pre_preserve - Occurs before any of the preservers are called. __NOTE:
    Can contain either XML data or error message at this point as both are
    considered valid results. Only occurs if XML is valid.__ Can be used as
    `eve_api.post_validate`.
- eve_api.pre_xml_error - Event occurs when Yapeal determines XML contains error
    message but has NOT preformed any action yet. __Good place to set
    `isActive = false` for a key or other wise manage keys etc depending on type
    of error.__
- eve_api.post_preserve - Preservers have finished and Yapeal is ready to move
    on. __NOTE: Like pre_retrieve this is per combo for account, char, corp
    sections. Only occurs if `oneShot()` returns true.__
- eve_api.done - Event occurs after all processing is done and per Eve API class
    is ready to return to the caller. Can be used for once per Eve API cleanup.

Just to be clear all the events from pre_retrieve to post_preserve can be issued
multiple times in the account, char, and corp sections and also in
eve/CharacterInfo if you have multiple keys, chars, or corps etc active for
those Eve APIs.

## Example

Here a simple test example of how to use one of the new events.

    #!/usr/bin/env php
    <?php
    namespace Yapeal;
    
    require_once __DIR__ . '/bin/bootstrap.php';
    use Psr\Log\LoggerInterface;
    use Yapeal\Container\PimpleContainer;
    use Yapeal\Event\EveApiEventInterface;
    use Yapeal\Event\YapealEventDispatcherInterface;
    
    $dic = new PimpleContainer();
    $yapeal = new Yapeal($dic);
    $yapeal->wire($dic);
    /**
     * @type YapealEventDispatcherInterface $yed
     */
    $yed = $dic['Yapeal.Event.Dispatcher'];
    $test = function (
        EveApiEventInterface $event,
        $eventName,
        YapealEventDispatcherInterface $yed
    ) {
        $data = $event->getData();
        $mess = 'Received event ' . $eventName . ' for Eve API '
                . $data->getEveApiSectionName() . '/' . $data->getEveApiName()
                . PHP_EOL;
        print $mess;
    };
    $yed->addListener('eve_api.pre_retrieve', $test);
    exit($yapeal->autoMagic());

This example uses a function for the callable but I would expect you to be use a
class method in actual production code of course so more like this:
`$yed->addListener('eve_api.pre_retrieve', ['myAppClass', 'myHandler']);`
