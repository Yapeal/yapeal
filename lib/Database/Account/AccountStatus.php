<?php
/**
 * Contains AccountStatus class.
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
namespace Yapeal\Database\Account;

use LogicException;
use PDO;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;

/**
 * Class AccountStatus
 */
class AccountStatus extends AbstractAccountSection
{
    use EveApiNameTrait, ValuesDatabasePreserverTrait, AttributesDatabasePreserverTrait;
    /**
     * @throws LogicException
     * @return array
     */
    protected function getActiveKeys()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredAccountStatus();
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            return [];
        }
    }
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
            $this->preserveToAccountStatus($xml, $ownerID)
                 ->preserveToMultiCharacterTraining($xml, $ownerID)
                 ->preserveToOffers($xml, $ownerID);
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
    protected function preserveToAccountStatus(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'keyID'        => $ownerID,
            'createDate'   => null,
            'logonCount'   => null,
            'logonMinutes' => null,
            'paidUntil'    => null
        ];
        $this->valuesPreserveData(
            $xml,
            $columnDefaults,
            'accountAccountStatus'
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
    protected function preserveToMultiCharacterTraining($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID'       => $ownerID,
            'trainingEnd' => null
        ];
        $tableName = 'accountMultiCharacterTraining';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithKeyID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//multiCharacterTraining/row'
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
    protected function preserveToOffers($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID'       => $ownerID,
            'offerID'     => null,
            'offeredDate' => null,
            'from'        => null,
            'to'          => null,
            'ISK'         => null
        ];
        $tableName = 'accountOffers';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithKeyID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//Offers/row'
        );
        return $this;
    }
}
