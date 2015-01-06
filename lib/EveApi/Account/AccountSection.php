<?php
/**
 * Contains AccountSection class.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Account;

use LogicException;
use PDO;
use PDOException;
use Psr\Log\LogLevel;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Database\EveSectionNameTrait;
use Yapeal\EveApi\AbstractCommonEveApi;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventDispatcherInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Event\LogEvent;

/**
 * Class AccountSection
 */
class AccountSection extends AbstractCommonEveApi implements
    EventSubscriberInterface, ServiceCallableInterface
{
    use EveSectionNameTrait;
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $priorityBase = -100;
        $serviceBase = 'Yapeal.EveApi.';
        $events = [
            $serviceBase . 'start' => ['eveApiStart', $priorityBase],
            $serviceBase . 'end' => ['eveApiEnd', $priorityBase]
        ];
        return $events;
    }
    /**
     * @inheritdoc
     */
    public static function injectCallable(ContainerInterface $dic)
    {
        $class = __CLASS__;
        $serviceName = str_replace('\\', '.', $class);
        $dic[$serviceName] = function () use ($dic, $class) {
            /**
             * @type AccountSection $callable
             */
            $callable = new $class();
            return $callable->setCsq($dic['Yapeal.Database.CommonQueries'])
                            ->setPdo($dic['Yapeal.Database.Connection']);
        };
        return $serviceName;
    }
    /**
     * @param EveApiEventInterface $event
     *
     * @return EveApiEventInterface
     */
    public function eveApiEnd(EveApiEventInterface $event)
    {
        return $event;
    }
    /**
     * @param EveApiEventInterface     $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $yed
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function eveApiStart(
        EveApiEventInterface $event,
        $eventName,
        EventDispatcherInterface $yed
    )
    {
        $this->setYed($yed);
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $this->getYed()
             ->dispatchLogEvent(
                 'Yapeal.Log.log',
                 new LogEvent(LogLevel::DEBUG, $mess)
             );
        $active = $this->getActive();
        if (empty($active)) {
            $mess = 'No active registered keys found';
            $this->getYed()
                 ->dispatchLogEvent(
                     'Yapeal.Log.log',
                     new LogEvent(LogLevel::INFO, $mess)
                 );
            return $this->getYed()
                        ->dispatchEveApiEvent('Yapeal.EveApi.end', $data);
        }
        $untilInterval = $data->getCacheInterval();
        foreach ($active as $key) {
            if ($this->cacheNotExpired(
                $data->getEveApiName(),
                $data->getEveApiSectionName(),
                $key['keyID']
            )
            ) {
                continue;
            }
            // Set arguments, reset interval, and clear xml data.
            $data->setEveApiArguments($key)
                 ->setCacheInterval($untilInterval)
                 ->setEveApiXml();
            $events = ['retrieve', 'transform', 'validate', 'preserve'];
            foreach ($events as $eventSuffix) {
//                $eventName = sprintf(
//                    'Yapeal.EveApi.%1$s.%2$s.%3$s',
//                    $data->getEveApiSectionName(),
//                    $data->getEveApiName(),
//                    $eventSuffix
//                );
//                $event = $this->getYed()
//                              ->dispatchEveApiEvent($eventName, $data);
//                $data = $event->getData();
//                if (false === $data->getEveApiXml()) {
//                    $mess = sprintf(
//                        '%5$s: Could NOT %4$s data from Eve API %1$s/%2$s for %3$s',
//                        strtolower($data->getEveApiSectionName()),
//                        $data->getEveApiName(),
//                        $key['keyID'],
//                        $eventSuffix,$eventName
//                    );
//                    $this->getYed()
//                         ->dispatchLogEvent(
//                             'Yapeal.Log.log',
//                             new LogEvent(LogLevel::NOTICE, $mess)
//                         );
//                    continue 2;
//                }
                $eventName = 'Yapeal.EveApi.' . $eventSuffix;
                $event = $this->getYed()
                              ->dispatchEveApiEvent($eventName, $data);
                $data = $event->getData();
                if (false === $data->getEveApiXml()) {
                    $mess = sprintf(
                        '%5$s: Could NOT %4$s data from Eve API %1$s/%2$s for %3$s',
                        $data->getEveApiSectionName(),
                        $data->getEveApiName(),
                        $key['keyID'],
                        $eventSuffix,
                        $eventName
                    );
                    $this->getYed()
                         ->dispatchLogEvent(
                             'Yapeal.Log.log',
                             new LogEvent(LogLevel::NOTICE, $mess)
                         );
                    continue;
                }
            }
            $this->updateCachedUntil($data, $key['keyID']);
        }
        $data->setCacheInterval($untilInterval)
             ->setEveApiArguments([])
             ->setEveApiXml();
        return $this->getYed()
                    ->dispatchEveApiEvent('Yapeal.EveApi.end', $data);
    }
    /**
     * @throws LogicException
     * @return array
     */
    protected function getActive()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredKeys();
        $this->getYed()
             ->dispatchLogEvent(
                 'Yapeal.Log.log',
                 new LogEvent(LogLevel::DEBUG, $sql)
             );
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
            $this->getYed()
                 ->dispatchLogEvent(
                     'Yapeal.Log.log',
                     new LogEvent(LogLevel::WARNING, $mess)
                 );
            return [];
        }
    }
}
