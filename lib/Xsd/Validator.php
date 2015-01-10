<?php
/**
 * Contains Validator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsd;

use DOMDocument;
use SimpleXMLElement;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Event\ContainerAwareEventDispatcherInterface;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Validator
 */
class Validator implements EventSubscriberInterface, ServiceCallableInterface
{
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $priorityBase = -PHP_INT_MAX;
        $events = [
            'Yapeal.EveApi.validate' => [
                'eveApiValidate',
                $priorityBase
            ]
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
             * @type Validator $callable
             */
            $callable = new $class();
            return $callable;
        };
        return $serviceName;
    }
    /**
     * @param EveApiEventInterface                   $event
     * @param string                                 $eventName
     * @param ContainerAwareEventDispatcherInterface $yed
     *
     * @return EveApiEventInterface
     */
    public function eveApiValidate(
        EveApiEventInterface $event,
        $eventName,
        ContainerAwareEventDispatcherInterface $yed
    )
    {
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            __CLASS__
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $fileNames = sprintf(
            '%3$s/%1$s/%2$s.xsd,%3$s/%2$s.xsd,%3$s/%1$s/%1$s.xsd,%3$s/common.xsd',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            str_replace('\\', '/', __DIR__)
        );
        foreach (explode(',', $fileNames) as $fileName) {
            if (!is_readable($fileName) || !is_file($fileName)) {
                continue;
            }
            $mess = 'Using Xsd file ' . $fileName;
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            $oldErrors = libxml_use_internal_errors(true);
            libxml_clear_errors();
            $dom = new DOMDocument();
            $dom->loadXML($data->getEveApiXml());
            if (!$dom->schemaValidate($fileName)) {
                foreach (libxml_get_errors() as $error) {
                    $yed->dispatchLogEvent(
                        'Yapeal.Log.log',
                        Logger::INFO,
                        $error->message
                    );
                }
                libxml_clear_errors();
                libxml_use_internal_errors($oldErrors);
                return $event->setHandled()
                             ->stopPropagation();
            }
            libxml_clear_errors();
            libxml_use_internal_errors($oldErrors);
            if (false !== strpos($data->getEveApiXml(), '<error')) {
                $event = $this->emitXmlErrorEvents($event, $yed);
            }
            $mess = sprintf(
                'Finished %1$s event for %2$s/%3$s',
                $eventName,
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return $event->setHandled()
                         ->stopPropagation();
        }
        $mess = sprintf(
            'Failed to validate data for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        return $event->setHandled()
                     ->stopPropagation();
    }
    /**
     * @param EveApiReadWriteInterface               $data
     * @param ContainerAwareEventDispatcherInterface $yed
     */
    protected function checkEveApiXmlError(
        EveApiReadWriteInterface &$data,
        ContainerAwareEventDispatcherInterface $yed
    )
    {
        if (strpos($data->getEveApiXml(), '<error') === false) {
            return;
        }
        $eventSuffix = 'xmlError';
        $eventNames = sprintf(
            '%3$s.%1$s.%2$s.%4$s,%3$s.%1$s.%4$s,%3$s.%4$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            'Yapeal.EveApi',
            $eventSuffix
        );
        foreach (explode(',', $eventNames) as $eventName) {
            $event = $yed
                ->dispatchEveApiEvent($eventName, $data);
            $data = $event->getData();
        }
        $simple = new SimpleXMLElement($data->getEveApiXml());
        if (!isset($simple->error)) {
            return;
        }
        $code = (int)$simple->error['code'];
        $mess = sprintf(
            'Eve Error (%3$s): Received from API %1$s/%2$s - %4$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $code,
            (string)$simple->error
        );
        if ($code < 200) {
            if (strpos($mess, 'retry after') !== false) {
                $data->setCacheInterval(
                    strtotime(substr($mess, -19) . '+00:00') - time()
                );
            }
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return;
        }
        if ($code < 300) { // API key errors.
            $mess .= ' for keyID: ' . $data->getEveApiArgument('keyID');
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::ERROR, $mess);
            $data->setCacheInterval(86400);
            return;
        }
        if ($code > 903 && $code < 905) { // Major application or Yapeal error.
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::ALERT, $mess);
            $data->setCacheInterval(86400);
            return;
        }
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
        $data->setCacheInterval(300);
    }
    /**
     * @param EveApiEventInterface                   $event
     * @param ContainerAwareEventDispatcherInterface $yed
     *
     * @return EveApiEventInterface
     */
    protected function emitXmlErrorEvents(
        EveApiEventInterface $event,
        ContainerAwareEventDispatcherInterface $yed
    )
    {
        $data = $event->getData();
        $eventSuffix = 'xmlError';
        $eventNames = sprintf(
            '%3$s.%1$s.%2$s.%4$s,%3$s.%1$s.%4$s,%3$s.%4$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            'Yapeal.EveApi',
            $eventSuffix
        );
        foreach (explode(',', $eventNames) as $eventName) {
            $event = $yed
                ->dispatchEveApiEvent($eventName, $data);
            if ($event->isHandled()) {
                break;
            }
            $data = $event->getData();
        }
        return $event->setData($data);
    }
}
