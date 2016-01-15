<?php
/**
 * Contains ContactList class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Corp;

use LogicException;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class ContactList
 */
class ContactList extends AbstractCorpSection
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
            $this->preserverToAllianceContactLabels($xml, $ownerID)
                 ->preserverToAllianceContactList($xml, $ownerID)
                 ->preserverToCorporateContactLabels($xml, $ownerID)
                 ->preserverToCorporateContactList($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
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
     * @throws LogicException
     * @return self
     */
    protected function preserverToAllianceContactLabels($xml, $ownerID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'labelID' => null,
            'name'    => null
        ];
        $tableName = 'charAllianceContactLabels';
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
            '//allianceContactLabels/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @internal param int $key
     *
     * @return self
     */
    protected function preserverToAllianceContactList(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'ownerID'     => $ownerID,
            'contactID'   => null,
            'contactName' => null,
            'contactTypeID' => null,
            'labelMask'     => null,
            'standing'    => null
        ];
        $tableName = 'corpAllianceContactList';
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
     * @param $xml
     * @param $ownerID
     *
     * @return $this
     * @throws LogicException
     */
    protected function preserverToCorporateContactLabels($xml, $ownerID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'labelID' => null,
            'name'    => null
        ];
        $tableName = 'charCorporateContactLabels';
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
            '//corporateContactLabels/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @internal param int $key
     *
     * @return self
     */
    protected function preserverToCorporateContactList(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'ownerID'     => $ownerID,
            'contactID'   => null,
            'contactName' => null,
            'contactTypeID' => null,
            'labelMask'     => null,
            'standing'    => null
        ];
        $tableName = 'corpCorporateContactList';
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
     * @type int $mask
     */
    protected $mask = 16;
}
