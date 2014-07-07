<?php
/**
 * Contains ContactList class.
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
namespace Yapeal\Database\Char;

use LogicException;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class ContactList
 */
class ContactList extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToContactList($xml, $ownerID);
            $this->preserverToCorporateContactList($xml, $ownerID);
            $this->preserverToAllianceContactList($xml, $ownerID);
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
     * @throws LogicException
     * @return self
     */
    protected function preserverToAllianceContactList($xml, $ownerID)
    {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'contactID' => null,
            'contactName' => null,
            'contactTypeID' => null,
            'standing' => null
        );
        $tableName = 'charAllianceContactList';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//allianceContactList/row'
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
    protected function preserverToContactList($xml, $ownerID)
    {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'contactID' => null,
            'contactName' => null,
            'contactTypeID' => null,
            'inWatchlist' => '0',
            'standing' => null
        );
        $tableName = 'charContactList';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//contactList/row'
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
    protected function preserverToCorporateContactList($xml, $ownerID)
    {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'contactID' => null,
            'contactName' => null,
            'contactTypeID' => null,
            'standing' => null
        );
        $tableName = 'charCorporateContactList';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//corporateContactList/row'
        );
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 16;
}
