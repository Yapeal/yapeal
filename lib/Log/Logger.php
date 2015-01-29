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
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Event\LogEventInterface;

/**
 * Class Logger
 */
class Logger extends MLogger implements
    ServiceCallableInterface,
    EventSubscriberInterface,
    EventAwareLoggerInterface
{
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $events = ['Yapeal.Log.log' => ['logEvent', -PHP_INT_MAX]];
        return $events;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function injectCallable(ContainerInterface &$dic)
    {
        $class = __CLASS__;
        $serviceName = str_replace('\\', '.', $class);
        if (empty($dic[$serviceName])) {
            $dic[$serviceName] = function () use ($dic, $class) {
                $group = [];
                if (PHP_SAPI === 'cli') {
                    $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                        'php://stderr', 100
                    );
                }
                $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                    $dic['Yapeal.Log.logDir'] . $dic['Yapeal.Log.fileName'],
                    100
                );
                return new $class(
                    $dic['Yapeal.Log.channel'],
                    [
                        new $dic['Yapeal.Log.Handlers.fingersCrossed'](
                            new $dic['Yapeal.Log.Handlers.group'](
                                $group
                            ),
                            (int)$dic['Yapeal.Log.threshold'],
                            (int)$dic['Yapeal.Log.bufferSize']
                        )
                    ]
                );
            };
        }
        return $serviceName;
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
