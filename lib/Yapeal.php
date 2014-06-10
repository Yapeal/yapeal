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

use Guzzle\Http\Client;
use Monolog\ErrorHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\WiringInterface;
use Yapeal\Database\Account\APIKeyInfo;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Xml\EveApiXmlData;
use Yapeal\Xml\FileCachePreserver;
use Yapeal\Xml\FileCacheRetriever;
use Yapeal\Xml\GroupPreserver;
use Yapeal\Xml\GroupRetriever;
use Yapeal\Xml\GuzzleNetworkRetriever;

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
        $dic['Yapeal.Error.Logger'];
        /**
         * @var LoggerInterface $logger
         */
        $logger = $dic['Yapeal.Log.Logger'];
        $logger->info('Let the magic begin!');
        try {
            /**
             * @var PDO $pdo
             */
            $pdo = $dic['Yapeal.Database.Connection'];
            /**
             * @var CommonSqlQueries $csq
             */
            $csq = $dic['Yapeal.Database.CommonQueries'];
            $sql = $csq->getActiveApis();
            $logger->debug($sql);
            /**
             * @var PDOStatement $smt
             */
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                $logger->warning('No active Eve APIs will exist now');
                return 1;
            }
            foreach ($result as $record) {
                $className =
                    'Yapeal\\Database\\' . ucfirst($record['section']) . '\\'
                    . $record['api'];
                if (!class_exists($className)) {
                    $logger->info('Class not found ' . $className);
                    continue;
                }
                /**
                 * @var APIKeyInfo $class
                 */
                $class = new $className($pdo, $logger, $csq);
                $class->autoMagic(
                    new EveApiXmlData($record['api'], $record['section']),
                    $dic['Yapeal.Xml.Retriever'],
                    $dic['Yapeal.Xml.Preserver']
                );
            }
        } catch (\PDOException $exc) {
            $mess = 'Could not access utilEveApi table';
            $logger->error($mess, array('exception' => $exc));
            return 1;
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
        $this->wireErrorLogger($dic);
        $this->wireLogLogger($dic);
        $this->wireDatabase($dic);
        $this->wireCommonSqlQueries($dic);
        $this->wireRetriever($dic);
        $this->wirePreserver($dic);
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
    /**
     * @param ContainerInterface $dic
     */
    private function wireCommonSqlQueries(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Database.CommonQueries'])) {
            return;
        }
        $dic['Yapeal.Database.CommonQueries'] = function ($dic) {
            return new CommonSqlQueries(
                $dic['Yapeal.Database.database'],
                $dic['Yapeal.Database.tablePrefix']
            );
        };
    }
    /**
     * @param ContainerInterface $dic
     *
     * @throws YapealDatabaseException
     */
    private function wireDatabase(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Database.Connection'])) {
            return;
        }
        $defaults = array(
            'Yapeal.Database.platform' => 'mysql',
            'Yapeal.Database.class' => 'PDO',
            'Yapeal.Database.hostName' => 'localhost',
            'Yapeal.Database.database' => 'yapeal',
            'Yapeal.Database.password' => 'secret',
            'Yapeal.Database.tablePrefix' => '',
            'Yapeal.Database.userName' => 'YapealUser'
        );
        foreach ($defaults as $setting => $default) {
            if (empty($dic[$setting])) {
                $dic[$setting] = $default;
            }
        }
        if ($dic['Yapeal.Database.platform'] != 'mysql') {
            $mess = 'Unknown platform was given '
                . $dic['Yapeal.Database.platform'];
            throw new YapealDatabaseException($mess);
        }
        $dic['Yapeal.Database.Connection'] = function ($dic) {
            $dsn = $dic['Yapeal.Database.platform'] . ':host='
                . $dic['Yapeal.Database.hostName']
                . ';charset=utf8';
            if (!empty($dic['Yapeal.Database.port'])) {
                $dsn .= ';port=' . $dic['Yapeal.Database.port'];
            }
            /**
             * @var PDO $database
             */
            $database = new $dic['Yapeal.Database.class'](
                $dsn,
                $dic['Yapeal.Database.userName'],
                $dic['Yapeal.Database.password']
            );
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
     * @param ContainerInterface $dic
     */
    private function wireErrorLogger(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Error.Logger'])) {
            return;
        }
        $defaults = array(
            'Yapeal.Error.class' => 'Monolog\\ErrorHandler',
            'Yapeal.Error.channel' => 'php',
            'Yapeal.Error.fileName' => 'yapeal.log',
            'Yapeal.Error.logDir' => $dic['Yapeal.baseDir'] . 'log/',
            'Yapeal.Error.threshold' => 500
        );
        foreach ($defaults as $setting => $default) {
            if (empty($dic[$setting])) {
                $dic[$setting] = $default;
            }
        }
        if (empty($dic['Yapeal.Error.loggerName'])) {
            $loggerName = 'Monolog\\Logger';
            if (isset($dic['Yapeal.Log.class'])) {
                $loggerName = $dic['Yapeal.Log.class'];
            }
            $dic['Yapeal.Error.loggerName'] = $loggerName;
        }
        /**
         * @param $dic
         *
         * @throws \RuntimeException
         * @return ErrorHandler
         */
        $dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $dic['Yapeal.Error.loggerName'](
                $dic['Yapeal.Error.channel']
            );
            $group = array();
            /**
             * @var Logger $logger
             */
            if (PHP_SAPI == 'cli') {
                $group[] = new StreamHandler('php://stderr', 100);
            }
            $group[] = new StreamHandler(
                $dic['Yapeal.Error.logDir']
                . $dic['Yapeal.Error.fileName'],
                100
            );
            $logger->pushHandler(
                new FingersCrossedHandler(
                    new GroupHandler($group),
                    $dic['Yapeal.Error.threshold'],
                    25
                )
            );
            /**
             * @var ErrorHandler $error
             */
            $error = $dic['Yapeal.Error.class'];
            $error::register(
                $logger,
                array(),
                $dic['Yapeal.Error.threshold'],
                false
            );
            return $error;
        };
    }
    /**
     * @param ContainerInterface $dic
     */
    private function wireLogLogger(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Log.Logger'])) {
            return;
        }
        $defaults = array(
            'Yapeal.Log.class' => 'Monolog\\Logger',
            'Yapeal.Log.channel' => 'yapeal',
            'Yapeal.Log.logDir' => $dic['Yapeal.baseDir'] . 'log/',
            'Yapeal.Log.fileName' => 'yapeal.log',
            'Yapeal.Log.threshold' => 100
        );
        foreach ($defaults as $setting => $default) {
            if (empty($dic[$setting])) {
                $dic[$setting] = $default;
            }
        }
        $dic['Yapeal.Log.Logger'] = function ($dic) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $dic['Yapeal.Log.class']($dic['Yapeal.Log.channel']);
            $group = array();
            /**
             * @var Logger $logger
             */
            if (PHP_SAPI == 'cli') {
                $group[] = new StreamHandler('php://stderr', 100);
            }
            $group[] = new StreamHandler(
                $dic['Yapeal.Log.logDir'] . $dic['Yapeal.Log.fileName'],
                100
            );
            $logger->pushHandler(
                new FingersCrossedHandler(
                    new GroupHandler($group), $dic['Yapeal.Log.threshold'], 25
                )
            );
            return $logger;
        };
    }
    /**
     * @param ContainerInterface $dic
     */
    private function wirePreserver(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Xml.Preserver'])) {
            return;
        }
        $dic['Yapeal.Xml.Preserver'] = function ($dic) {
            $preservers = array(
                new FileCachePreserver(
                    $dic['Yapeal.Log.Logger'],
                    $dic['Yapeal.baseDir'] . 'cache/'
                )
            );
            return new GroupPreserver($dic['Yapeal.Log.Logger'], $preservers);
        };
    }
    /**
     * @param ContainerInterface $dic
     */
    private function wireRetriever(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Xml.Retriever'])) {
            return;
        }
        $dic['Yapeal.Xml.Retriever'] = function ($dic) {
            $headers = array(
                'Accept' => 'text/xml,application/xml,application/xhtml+xml;'
                    . 'q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;'
                    . 'q=0.6,*/*;q=0.5',
                'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
                'Accept-Encoding' => 'gzip',
                'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
                'Connection' => 'Keep-Alive',
                'Keep-Alive' => '300'
            );
            $defaults = array(
                'headers' => $headers,
                'timeout' => 10,
                'connect_timeout' => 30,
                'verify' => $dic['Yapeal.baseDir'] . 'config/eveonline.crt',
            );
            $retrievers = array(
                new FileCacheRetriever(
                    $dic['Yapeal.Log.Logger'],
                    $dic['Yapeal.baseDir'] . 'cache/'
                ),
                new GuzzleNetworkRetriever(
                    $dic['Yapeal.Log.Logger'],
                    new Client(
                        'https://api.eveonline.com',
                        array('defaults' => $defaults)
                    )
                )
            );
            return new GroupRetriever($dic['Yapeal.Log.Logger'], $retrievers);
        };
    }
}
