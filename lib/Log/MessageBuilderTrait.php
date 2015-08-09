<?php
/**
 * Contains MessageBuilderTrait Trait.
 *
 * PHP version 5.5
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
namespace Yapeal\Log;

use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait MessageBuilderTrait
 */
trait MessageBuilderTrait
{
    /**
     * @param string                   $messagePrefix
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function createEventMessage($messagePrefix, EveApiReadWriteInterface $data, $eventName)
    {
        $mess = $messagePrefix . ' %3$s event of Eve API %1$s/%2$s';
        $subs = [lcfirst($data->getEveApiSectionName()), $data->getEveApiName(), $eventName];
        if ($data->hasEveApiArgument('keyID')) {
            $mess .= ' for keyID = %4$s';
            $subs[] = $data->getEveApiArgument('keyID');
            if ($data->hasEveApiArgument('characterID')) {
                $mess .= ' and characterID = %5$s';
                $subs[] = $data->getEveApiArgument('characterID');
            }
        }
        return vsprintf($mess, $subs);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getEmittingEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Emitting:';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getNonHandledEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Nothing reported handling:';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     * @param string                   $location
     *
     * @return string
     * @throws \LogicException
     */
    protected function getReceivedEventMessage(EveApiReadWriteInterface $data, $eventName, $location)
    {
        $messagePrefix = sprintf('Received in %s:', $location);
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getSufficientlyHandledEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Sufficiently handled:';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
}
