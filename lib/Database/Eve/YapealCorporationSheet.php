<?php
/**
 * Contains YapealCorporationSheet class.
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
use PDO;
use PDOException;
use Yapeal\Database\Corp\CorporationSheet;

/**
 * Class YapealCorporationSheet
 */
class YapealCorporationSheet extends CorporationSheet
{
    /**
     * @throws LogicException
     * @return array
     */
    protected function getActiveCorporations()
    {
        $sql = $this->getCsq()
                    ->getMemberCorporationIDsExcludingAccountCorporations();
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of member corporations';
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            return [];
        }
    }
    /**
     * @return string
     */
    protected function getApiName()
    {
        return 'CorporationSheet';
    }
    /**
     * @return string
     */
    protected function getSectionName()
    {
        return 'corp';
    }
    /**
     * @type int $mask
     */
    protected $mask = 0;
}
