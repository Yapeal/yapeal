<?php
/**
 * Contains APIKeyInfo class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database\Account;

use PDOException;
use SimpleXMLIterator;
use Yapeal\Database\ApiNameTrait;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AbstractAccountSection
{
    use ApiNameTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserveToAPIKeyInfo($xml, $ownerID);
            $this->preserveToCharacters($xml);
            $this->preserveToKeyBridge($xml, $ownerID);
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
                 ->warning($mess, array('exception' => $exc));
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
    protected function preserveToAPIKeyInfo(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'keyID' => $ownerID,
            'accessMask' => null,
            'expires' => '2038-01-19 03:14:07',
            'type' => null
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('accountAPIKeyInfo')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml, '//key');
        return $this;
    }
    /**
     * @param string $xml
     */
    protected function preserveToCharacters(
        $xml
    ) {
        $columnDefaults = array(
            'characterID' => null,
            'characterName' => null,
            'corporationID' => null,
            'corporationName' => null,
            'allianceID' => null,
            'allianceName' => null,
            'factionID' => null,
            'factionName' => null
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('accountCharacters')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml, '//row');
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserveToKeyBridge(
        $xml,
        $ownerID
    ) {
        $simple = new SimpleXMLIterator($xml);
        $chars = $simple->xpath('//row');
        $rows = array();
        foreach ($chars as $aRow) {
            $rows[] = $ownerID;
            $rows[] = $aRow['characterID'];
        }
        $sql = $this->getCsq()
                    ->getUpsert(
                        'accountKeyBridge',
                        array('keyID', 'characterID'),
                        count($chars)
                    );
        $this->getLogger()
            ->info($sql);
        $stmt = $this->getPdo()
                     ->prepare($sql);
        $stmt->execute($rows);
    }
}
