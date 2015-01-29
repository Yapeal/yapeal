<?php
/**
 * Contains Transformer class.
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
namespace Yapeal\Xsl;

use DOMDocument;
use SimpleXMLElement;
use tidy;
use XSLTProcessor;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Event\ContainerAwareEventDispatcherInterface;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Log\Logger;

/**
 * Class Transformer
 */
class Transformer implements EventSubscriberInterface, ServiceCallableInterface
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
            'Yapeal.EveApi.transform' => [
                'eveApiTransform',
                $priorityBase
            ]
        ];
        return $events;
    }
    /**
     * @inheritdoc
     */
    public static function injectCallable(ContainerInterface &$dic)
    {
        $class = __CLASS__;
        $serviceName = str_replace('\\', '.', $class);
        $dic[$serviceName] = function () use ($class) {
            /**
             * @type Transformer $callable
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
    public function eveApiTransform(
        EveApiEventInterface $event,
        $eventName,
        ContainerAwareEventDispatcherInterface $yed
    ) {
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            __CLASS__
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $fileNames
            = sprintf(
                '%3$s/%1$s/%2$s.xsl,%3$s/%2$s.xsl,%3$s/%1$s/%1$s.xsl,%3$s/common.xsl',
                $data->getEveApiSectionName(),
                $data->getEveApiName(),
                str_replace('\\', '/', __DIR__)
            );
        foreach (explode(',', $fileNames) as $fileName) {
            if (!is_readable($fileName) || !is_file($fileName)) {
                continue;
            }
            $mess = 'Using Xsl file ' . $fileName;
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            $xslt = new XSLTProcessor();
            $oldErrors = libxml_use_internal_errors(true);
            libxml_clear_errors();
            $dom = new DOMDocument();
            $dom->load($fileName);
            $xslt->importStylesheet($dom);
            $xml
                = $xslt->transformToXml(
                    new SimpleXMLElement($data->getEveApiXml())
                );
            if (false === $xml) {
                foreach (libxml_get_errors() as $error) {
                    $yed->dispatchLogEvent(
                        'Yapeal.Log.log',
                        Logger::DEBUG,
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
            $config = [
                'indent'        => true,
                'indent-spaces' => 4,
                'output-xml'    => true,
                'input-xml'     => true,
                'wrap'          => '1000'
            ];
            // Tidy
            $tidy = new tidy();
            $data->setEveApiXml($tidy->repairString($xml, $config, 'utf8'));
            $event->setData($data);
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
            'Failed to transform data for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        return $event->setHandled()
                     ->stopPropagation();
    }
}
