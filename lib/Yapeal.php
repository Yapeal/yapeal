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
use PDOStatement;
use Psr\Log\LoggerInterface;
use Yapeal\Configuration\Wiring;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\WiringInterface;
use Yapeal\Database\Account\APIKeyInfo;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Xml\EveApiXmlData;

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
     *
     * @return int Returns 0 if everything was fine else something >= 1 for any
     * errors.
     */
    public function autoMagic()
    {
        $dic = $this->getDic();
        /**
         * @var LoggerInterface $logger
         */
        $logger = $dic['Yapeal.Log.Logger'];
        $logger->info('Let the magic begin!');
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $dic['Yapeal.Database.CommonQueries'];
        $sql = $csq->getActiveApis();
        $logger->info($sql);
        try {
            /**
             * @var PDO $pdo
             */
            $pdo = $dic['Yapeal.Database.Connection'];
            /**
             * @var PDOStatement $smt
             */
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $exc) {
            $mess = 'Could not access utilEveApi table';
            $logger->error($mess, array('exception' => $exc));
            return 1;
        }
        if (empty($result)) {
            $logger->warning('Exiting no active Eve APIs found');
            return 1;
        }
        foreach ($result as $record) {
            $className = sprintf(
                'Yapeal\\Database\\%1$s\\%2$s',
                ucfirst($record['sectionName']),
                $record['apiName']
            );
            if (!class_exists($className)) {
                $logger->debug('Class not found ' . $className);
                continue;
            }
            /**
             * @var APIKeyInfo $class
             */
            $class = new $className($pdo, $logger, $csq);
            $class->autoMagic(
                new EveApiXmlData(
                    $record['apiName'], $record['sectionName']
                ),
                $dic['Yapeal.Xml.Retriever'],
                $dic['Yapeal.Xml.Preserver'],
                (int)$record['interval']
            );
        }
        return 0;
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
     *
     * @throws YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.cwd'])) {
            $dic['Yapeal.cwd'] = str_replace('\\', '/', dirname(__DIR__)) . '/';
        }
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] =
                str_replace('\\', '/', dirname(__DIR__)) . '/';
        }
        $wiring = new Wiring($dic);
        $wiring->wireDefaults()
               ->wireConfiguration();
        $dic['Yapeal.Config.Parser'];
        $wiring->wireErrorLogger();
        $dic['Yapeal.Error.Logger'];
        $wiring->wireLogLogger()
               ->wireDatabase()
               ->wireCommonSqlQueries()
               ->wireRetriever()
               ->wirePreserver();
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
