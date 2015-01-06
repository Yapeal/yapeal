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
namespace Yapeal\Database\Account;

use LogicException;
use PDOException;
use SimpleXMLIterator;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Event\EventSubscriberInterface;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AbstractAccountSection implements
    EventSubscriberInterface
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and
     *  respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority),
     *  array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $class = str_replace('\\', '/', __CLASS__);
        $eventBase = sprintf(
            'Eve.Api.%1$s.%2$s.',
            basename(dirname($class)),
            basename($class)
        );
        return [$eventBase . 'Start' => ['autoMagic', -100]];
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserve($xml, $ownerID)
    {
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
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
            );
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserveToAPIKeyInfo($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID' => $ownerID,
            'accessMask' => null,
            'expires' => '2038-01-19 03:14:07',
            'type' => null
        ];
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'accountAPIKeyInfo',
            '//key'
        );
        return $this;
    }
    /**
     * @param string $xml
     *
     * @return self
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
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'accountCharacters'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserveToKeyBridge($xml, $ownerID)
    {
        $chars = (new SimpleXMLIterator($xml))->xpath('//row');
        if (count($chars) == 0) {
            return $this;
        }
        $rows = [];
        foreach ($chars as $aRow) {
            $rows[] = $ownerID;
            $rows[] = $aRow['characterID'];
        }
        $sql = $this->getCsq()
                    ->getUpsert(
                        'accountKeyBridge',
                        ['keyID', 'characterID'],
                        count($chars)
                    );
        $this->getLogger()
             ->info($sql);
        $stmt = $this->getPdo()
                     ->prepare($sql);
        $stmt->execute($rows);
        return $this;
    }
}
