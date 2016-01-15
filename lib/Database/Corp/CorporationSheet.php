<?php
/**
 * Contains CorporationSheet class.
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
 */
namespace Yapeal\Database\Corp;

use LogicException;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class CorporationSheet
 */
class CorporationSheet extends AbstractCorpSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait, ValuesDatabasePreserverTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    ) {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $corp = $data->getEveApiArguments();
        $corpID = $corp['corporationID'];
        // Can NOT include corporationID or only get public info.
        if (array_key_exists('keyID', $corp)) {
            unset($corp['corporationID']);
            $data->setEveApiArguments($corp);
        }
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $corpID
            );
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $corpID
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $preservers->preserveEveApi($data);
        // No need / way to preserve XML errors to the database with normal
        // preserve.
        if ($this->isEveApiXmlError($data, $interval)) {
            return true;
        }
        return $this->preserve($data->getEveApiXml(), $corpID);
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return bool
     */
    protected function preserve(
        $xml,
        $ownerID
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToCorporationSheet($xml, $ownerID)
                 ->preserverToDivisions($xml, $ownerID)
                 ->preserverToWalletDivisions($xml, $ownerID)
                 ->preserverToLogo($xml, $ownerID);
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
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationSheet($xml, $ownerID)
    {
        $columnDefaults = [
            'allianceID'      => '0',
            'allianceName'    => '',
            'ceoID'           => null,
            'ceoName'         => null,
            'corporationID'   => $ownerID,
            'corporationName' => null,
            'description'     => null,
            'factionID'       => '0',
            'factionName'     => '',
            'memberCount'     => null,
            'memberLimit'     => '0',
            'shares'          => null,
            'stationID'       => null,
            'stationName'     => null,
            'taxRate'         => null,
            'ticker'          => null,
            'url'             => null
        ];
        $this->valuesPreserveData(
            $xml,
            $columnDefaults,
            'corpCorporationSheet'
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
    protected function preserverToDivisions($xml, $ownerID)
    {
        $columnDefaults = [
            'ownerID'     => $ownerID,
            'accountKey'  => null,
            'description' => null
        ];
        $tableName = 'corpDivisions';
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
            '//divisions/row'
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
    protected function preserverToLogo($xml, $ownerID)
    {
        $columnDefaults = [
            'ownerID'   => $ownerID,
            'color1'    => null,
            'color2'    => null,
            'color3'    => null,
            'graphicID' => null,
            'shape1'    => null,
            'shape2'    => null,
            'shape3'    => null
        ];
        $tableName = 'corpLogo';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->valuesPreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//logo/*'
        );
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToWalletDivisions($xml, $ownerID)
    {
        $columnDefaults = [
            'ownerID'     => $ownerID,
            'accountKey'  => null,
            'description' => null
        ];
        $tableName = 'corpWalletDivisions';
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
            '//walletDivisions/row'
        );
        return $this;
    }
    /**
     * @type int $mask
     */
    protected $mask = 8;
}
