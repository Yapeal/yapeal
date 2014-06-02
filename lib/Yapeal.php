<?php
/**
 * Contains Yapeal class.
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
namespace Yapeal;

use PDO;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\WiringInterface;

/**
 * Class Yapeal
 */
class Yapeal implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
        $this->wire($this->getDic());
    }
    /**
     * Starts Eve API processing
     */
    public function run()
    {
    }
    /**
     * @param ContainerInterface $value
     *
     * @return self
     */
    public function setDic(ContainerInterface $value)
    {
        $this->dic = $value;
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     */
    public function wire(ContainerInterface $dic)
    {
        $dic['Yapeal.Database.Connection'] = function ($dic) {
            if (empty($dic['Yapeal.Database.hostName'])) {
                $dic['Yapeal.Database.hostName'] = 'localhost';
            }
            if (empty($dic['Yapeal.Database.userName'])) {
                $dic['Yapeal.Database.userName'] = 'YapealUser';
            }
            if (empty($dic['Yapeal.Database.password'])) {
                $dic['Yapeal.Database.password'] = 'secret';
            }
            $dsn = 'mysql:host=' . $dic['Yapeal.Database.hostName']
                . ';charset=utf8';
            if (!empty($dic['Yapeal.Database.port'])) {
                $dsn .= ';port=' . $dic['Yapeal.Database.port'];
            }
            $database = new PDO(
                $dsn,
                $dic['Yapeal.Database.userName'],
                $dic['Yapeal.Database.password']
            );
            $database->exec("set session sql_mode='ANSI,TRADITIONAL'");
            $database->exec(
                'set session transaction isolation level serializable'
            );
            $database->exec("set session time_zone='+00:00'");
            $database->exec('set names utf8');
            return $database;
        };
    }
    /**
     * @var ContainerInterface
     */
    protected $dic;
    /**
     * @return array
     */
    protected function getActiveEveApiList()
    {
        $list = array();
        return $list;
    }
    /**
     * @return ContainerInterface
     */
    protected function getDic()
    {
        return $this->dic;
    }
}
