<?php
/**
 * Contains Logger class.
 *
 * PHP version 5.5
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use EventMediator\SubscriberInterface;
use Monolog\Logger as MLogger;
use Yapeal\Event\LogEventInterface;

/**
 * Class Logger
 */
class Logger extends MLogger implements SubscriberInterface, EventAwareLoggerInterface
{
    /**
     * @inheritdoc
     *
     * @api
     */
    public function getSubscribedEvents()
    {
        $events = ['Yapeal.Log.log' => ['logEvent', 'last']];
        return $events;
    }
    /**
     * @inheritdoc
     */
    public function logEvent(LogEventInterface $event)
    {
        $this->log(
            $event->getLevel(),
            $event->getMessage(),
            $event->getContext()
        );
    }
}
