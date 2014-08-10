<?php
/**
 * Contains ServerStatus class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Server;

use LogicException;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\EveSectionNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;

/**
 * Class ServerStatus
 *
 */
class ServerStatus extends AbstractCommonEveApi
{
    use EveApiNameTrait, EveSectionNameTrait, ValuesDatabasePreserverTrait;
    /**
     * @param string $xml
     *
     * @throws LogicException
     * @return bool
     */
    protected function preserveToServerStatus(
        $xml
    ) {
        $columnDefaults = array(
            'serverName' => 'Tranquility',
            'serverOpen' => null,
            'onlinePlayers' => null
        );
        $tableName = 'serverServerStatus';
        $sql = $this->getCsq()
                    ->getDeleteFromTable($tableName);
        $this->getLogger()
             ->info($sql);
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->getPdo()
                 ->exec($sql);
            $this->valuesPreserveData(
                $xml,
                $columnDefaults,
                'serverServerStatus'
            );
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
            return false;
        }
        return true;
    }
}
