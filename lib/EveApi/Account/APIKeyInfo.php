<?php
/**
 * Contains APIKeyInfo class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Account;

use LogicException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\EveSectionNameTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventDispatcherInterface;
use Yapeal\Log\Logger;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AccountSection
{
    use EveApiNameTrait, EveSectionNameTrait;
    /**
     * @inheritDoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $eventName = str_replace('\\', '.', __CLASS__) . '.preserve';
        $events = [$eventName => ['eveApiPreserve', -PHP_INT_MAX]];
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
             * @type APIKeyInfo $callable
             */
            $callable = new $class();
            return $callable->setCsq($dic['Yapeal.Database.CommonQueries'])
                            ->setPdo($dic['Yapeal.Database.Connection']);
        };
        return $serviceName;
    }
    /**
     * @param EveApiEventInterface     $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $yed
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function eveApiPreserve(
        EveApiEventInterface $event,
        $eventName,
        EventDispatcherInterface $yed
    )
    {
        $this->setYed($yed);
        if ($event->isHandled()) {
            $mess = 'Received already handled event ' . $eventName;
            $this->getYed()
                 ->dispatchLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return $event;
        }
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            __CLASS__
        );
        $this->getYed()
             ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $fileName = sprintf(
            '%1$s/cache/%2$s/%3$s.xml',
            dirname(dirname(dirname(__DIR__))),
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        file_put_contents($fileName, $data->getEveApiXml());
        return $event;
    }
}
