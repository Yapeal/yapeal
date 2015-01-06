<?php
/**
 * Contains Logger class.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Monolog\Logger as MLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Event\LogEventInterface;

/**
 * Class Logger
 */
class Logger implements LoggerAwareInterface, ServiceCallableInterface,
    EventSubscriberInterface
{
    use LoggerAwareTrait;
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $events = ['Yapeal.Log.log' => ['logEvent', -100]];
        return $events;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function injectCallable(ContainerInterface $dic)
    {
        $class = __CLASS__;
        $serviceName = str_replace('\\', '.', $class);
        if (!isset($dic[$serviceName])) {
            $dic[$serviceName] = function () use ($dic, $class) {
                /**
                 * @type MLogger $logger
                 */
                $logger = new $dic['Yapeal.Log.Handlers.logger'](
                    $dic['Yapeal.Log.channel']
                );
                $group = [];
                if (PHP_SAPI == 'cli') {
                    $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                        'php://stderr', 100
                    );
                }
                $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                    $dic['Yapeal.Log.logDir'] . $dic['Yapeal.Log.fileName'],
                    100
                );
                $logger->pushHandler(
                    new $dic['Yapeal.Log.Handlers.fingersCrossed'](
                        new $dic['Yapeal.Log.Handlers.group']($group),
                        (int)$dic['Yapeal.Log.threshold'],
                        (int)$dic['Yapeal.Log.bufferSize']
                    )
                );
                return new $class($logger);
            };
        }
        return $serviceName;
    }
    /**
     * @param LogEventInterface $event
     */
    public function logEvent(LogEventInterface $event)
    {
        $this->logger->log(
            $event->getLevel(),
            $event->getMessage(),
            $event->getContext()
        );
    }
}
