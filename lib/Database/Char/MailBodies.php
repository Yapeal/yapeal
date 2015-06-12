<?php
/**
 * Contains MailBodies class.
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
namespace Yapeal\Database\Char;

use LogicException;
use PDO;
use PDOException;
use SimpleXMLElement;
use SimpleXMLIterator;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class MailBodies
 */
class MailBodies extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @throws LogicException
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->debug(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        /**
         * Update MailMessages List
         */
        $class = new MailMessages(
            $this->getPdo(), $this->getLogger(), $this->getCsq()
        );
        $class->autoMagic($data, $retrievers, $preservers, $interval);
        $active = $this->getActiveCharacters();
        if (0 === count($active)) {
            $this->getLogger()
                 ->info('No active characters found');
            return;
        }
        foreach ($active as $char) {
            $charID = $char['characterID'];
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $charID
            )
            ) {
                continue;
            }
            $mailIDs = $this->getActiveMails($charID);
            if (0 === count($mailIDs)) {
                $mess = 'No mail messages for ' . $charID;
                $this->getLogger()
                     ->info($mess);
                continue;
            }
            /**
             * @type array $mailGroups
             */
            $mailGroups = array_chunk($mailIDs, 1000);
            $untilInterval = $interval;
            foreach ($mailGroups as $mailGroup) {
                $mailIDs = [];
                foreach ($mailGroup as $mail) {
                    $mailIDs[] = $mail[0];
                }
                $mess = 'Mail IDs = ' . implode(',', $mailIDs);
                $this->getLogger()
                     ->debug($mess);
                $char['ids'] = implode(',', $mailIDs);
                $data->setEveApiArguments($char)
                     ->setEveApiXml();
                $untilInterval = $interval;
                if (!$this->oneShot(
                    $data,
                    $retrievers,
                    $preservers,
                    $untilInterval
                )
                ) {
                    continue 2;
                }
                if ($untilInterval > $interval) {
                    $untilInterval = $interval;
                }
                if ($untilInterval !== $interval) {
                    continue;
                }
            }
            $this->updateCachedUntil(
                $data->getEveApiXml(),
                $untilInterval,
                $charID
            );
        }
    }
    /**
     * @param string $ownerID
     *
     * @throws LogicException
     * @return array
     */
    protected function getActiveMails($ownerID)
    {
        $sql = $this->getCsq()
                    ->getActiveMailBodiesWithOwnerID($ownerID);
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_NUM);
        } catch (PDOException $exc) {
            $mess =
                'Could NOT get a list of active mail bodies for ' . $ownerID;
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            return [];
        }
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToMailBodies($xml, $ownerID)
    {
        $rows = (new SimpleXMLIterator($xml))->xpath('//row');
        if (0 === count($rows)) {
            return $this;
        }
        $columnNames = ['body', 'messageID', 'ownerID'];
        $maxRowCount = 1000;
        $rowCount = 0;
        $tableName = 'charMailBodies';
        $columns = [];
        /**
         * @type SimpleXMLElement $row
         */
        foreach ($rows as $row) {
            $columns[] = (string)$row;
            $columns[] = $row['messageID'];
            $columns[] = $ownerID;
            if (++$rowCount > $maxRowCount) {
                $this->flush($columns, $columnNames, $tableName, $rowCount);
                $columns = [];
                $rowCount = 0;
            }
        }
        $this->flush($columns, $columnNames, $tableName, $rowCount);
        return $this;
    }
    /**
     * @type int $mask
     */
    protected $mask = 512;
}
