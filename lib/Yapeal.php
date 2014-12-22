<?php
/**
 * Contains Yapeal class.
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
namespace Yapeal;

use DomainException;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerInterface;
use Yapeal\Configuration\Wiring;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\WiringInterface;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\Account\APIKeyInfo;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class Yapeal
 */
class Yapeal implements WiringInterface
{
    use FilePathNormalizerTrait;
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
         * @type LoggerInterface $logger
         */
        $logger = $dic['Yapeal.Log.Logger'];
        $logger->info('Let the magic begin!');
        /**
         * @type CommonSqlQueries $csq
         */
        $csq = $dic['Yapeal.Database.CommonQueries'];
        $sql = $csq->getActiveApis();
        $logger->info($sql);
        try {
            /**
             * @type PDO $pdo
             */
            $pdo = $dic['Yapeal.Database.Connection'];
            /**
             * @type PDOStatement $smt
             */
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could not access utilEveApi table';
            $logger->error($mess, ['exception' => $exc]);
            return 1;
        }
        $yed = $dic['Yapeal.Event.Dispatcher'];
        $data = new EveApiXmlData();
        // Always check APIKeyInfo.
        $class = new APIKeyInfo($pdo, $logger, $csq, $yed);
        $class->autoMagic(
            $data,
            $dic['Yapeal.Xml.Retriever'],
            $dic['Yapeal.Xml.Preserver'],
            300
        );
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
             * @type AbstractCommonEveApi $class
             */
            $class = new $className($pdo, $logger, $csq, $yed);
            $class->autoMagic(
                $data,
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
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function wire(ContainerInterface $dic)
    {
        $path = $this->getFpn()
                     ->normalizePath(dirname(__DIR__));
        if (empty($dic['Yapeal.cwd'])) {
            $dic['Yapeal.cwd'] = $path;
        }
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] = $path;
        }
        if (empty($dic['Yapeal.vendorParentDir'])) {
            $vendorPos = strpos($path, 'vendor/');
            if (false !== $vendorPos) {
                $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            }
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
            ->wirePreserver()
            ->wireEvents();
    }
    /**
     * @return array
     */
    protected function getActiveEveApiList()
    {
        $list = [];
        return $list;
    }
    /**
     * @return ContainerInterface
     */
    protected function getDic()
    {
        return $this->dic;
    }
    /**
     * @type ContainerInterface $dic
     */
    protected $dic;
}
