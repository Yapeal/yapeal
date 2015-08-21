<?php
/**
 * Contains APIKeyInfo class.
 *
 * PHP version 5.5
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

use PDOException;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Log\MessageBuilderTrait;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AccountSection
{
    use MessageBuilderTrait, PreserverTrait;
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function preserveEveApi(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $xml = $data->getEveApiXml();
        $ownerID = $data->getEveApiArgument('keyID');
        $this->getYem()
             ->triggerLogEvent(
                 'Yapeal.Log.log',
                 Logger::DEBUG,
                 $this->getReceivedEventMessage($data, $eventName, __CLASS__)
             );
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserveToAPIKeyInfo($xml, $ownerID)
                 ->preserveToCharacters($xml)
                 ->preserveToKeyBridge($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                strtolower($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $ownerID
            );
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess, ['exception' => $exc]);
            $this->getPdo()
                 ->rollBack();
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToAPIKeyInfo($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID' => $ownerID,
            'accessMask' => null,
            'expires' => '2038-01-19 03:14:07',
            'type' => null
        ];
        $this->attributePreserveData($xml, $columnDefaults, 'accountAPIKeyInfo', '//key');
        return $this;
    }
    /**
     * @param string $xml
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToCharacters($xml)
    {
        $columnDefaults = [
            'characterID' => null,
            'characterName' => null,
            'corporationID' => null,
            'corporationName' => null,
            'allianceID' => null,
            'allianceName' => null,
            'factionID' => null,
            'factionName' => null
        ];
        $this->attributePreserveData($xml, $columnDefaults, 'accountCharacters');
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToKeyBridge($xml, $ownerID)
    {
        $columnDefaults = ['keyID' => $ownerID, 'characterID' => null];
        $this->attributePreserveData($xml, $columnDefaults, 'accountKeyBridge');
        return $this;
    }
}
