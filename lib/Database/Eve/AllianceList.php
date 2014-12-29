<?php
/**
 * Contains AllianceList class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
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
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database\Eve;

use LogicException;
use PDOException;
use SimpleXMLIterator;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\EveSectionNameTrait;
use Yapeal\Event\EveApiEvent;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class AllianceList
 */
class AllianceList extends AbstractCommonEveApi
{
    use EveApiNameTrait, EveSectionNameTrait, AttributesDatabasePreserverTrait;
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
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    ) {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_RETRIEVE, $data);
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve Eve Api data for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->debug($mess);
            return false;
        }
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_TRANSFORM, $data);
        $this->xsltTransform($data);
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_VALIDATE, $data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'Data retrieved is invalid for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $this->getYed()
            ->dispatchEveApiEvent(EveApiEvent::PRE_PRESERVE, $data);
        $preservers->preserveEveApi($data);
        // No need / way to preserve XML errors to the database with normal
        // preserve.
        if ($this->isEveApiXmlError($data, $interval)) {
            return true;
        }
        return $this->preserve($data->getEveApiXml());
    }
    /**
     * @param SimpleXMLIterator $parent
     * @param string            $allianceID
     */
    protected function addMembers(SimpleXMLIterator $parent, $allianceID)
    {
        $this->columnDefaults = [
            'allianceID' => $allianceID,
            'corporationID' => null,
            'startDate' => null
        ];
        $tableName = 'eveMemberCorporations';
        /**
         * @type SimpleXMLIterator[] $kids
         */
        $kids = $parent->xpath('memberCorporations/row');
        foreach ($kids as $row) {
            // Replace empty values with any existing defaults.
            foreach ($this->columnDefaults as $key => $value) {
                if (is_null($value) || strlen($row[$key]) != 0) {
                    $this->columns[] = (string)$row[$key];
                    continue;
                }
                $this->columns[] = (string)$value;
            }
            if (++$this->rowCount > 1000) {
                $this->flush(
                    $this->columns,
                    array_keys($this->columnDefaults),
                    $tableName,
                    $this->rowCount
                );
                $this->columns = [];
                $this->rowCount = 0;
            }
        }
    }
    /**
     * @param string $xml
     *
     * @throws LogicException
     * @return bool
     */
    protected function preserve(
        $xml
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserveToAllianceList($xml);
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
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     *
     * @throws LogicException
     * @return self
     */
    protected function preserveToAllianceList($xml)
    {
        $columnDefaults = [
            'name' => null,
            'shortName' => null,
            'allianceID' => null,
            'executorCorpID' => null,
            'memberCount' => null,
            'startDate' => null
        ];
        $membersName = 'eveMemberCorporations';
        $tableName = 'eveAllianceList';
        $xPath = '//row[@allianceID]';
        $rows = (new SimpleXMLIterator($xml))->xpath($xPath);
        if (count($rows) == 0) {
            return $this;
        }
        $sql = $this->getCsq()
                    ->getDeleteFromTable($tableName);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $sql = $this->getCsq()
                    ->getDeleteFromTable($membersName);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $rowCount = 0;
        $columns = [];
        $this->rowCount = 0;
        $this->columns = [];
        /**
         * @type SimpleXMLIterator $row
         */
        foreach ($rows as $row) {
            foreach ($columnDefaults as $key => $value) {
                if ($key == 'allianceID') {
                    $this->addMembers($row, (string)$row[$key]);
                }
                $columns[] = (string)$row[$key];
            }
            if (++$rowCount > 1000) {
                $this->flush(
                    $columns,
                    array_keys($columnDefaults),
                    $tableName,
                    $rowCount
                );
                $columns = [];
                $rowCount = 0;
            }
        }
        $this->flush(
            $this->columns,
            array_keys($this->columnDefaults),
            $membersName,
            $this->rowCount
        );
        $this->flush(
            $columns,
            array_keys($columnDefaults),
            $tableName,
            $rowCount
        );
        return $this;
    }
    /**
     * @type string[] $columnDefaults
     */
    protected $columnDefaults;
    /**
     * @type array $columns
     */
    protected $columns;
    /**
     * @type int $rowCount
     */
    protected $rowCount;
}
