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
use Yapeal\Exception\YapealDatabaseException;

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
    public function run()
    {
        $dic = $this->getDic();
        $dic['Yapeal.Error.Logger'];
        /**
         * @var LoggerInterface $logger
         */
        $logger = $dic['Yapeal.Log.Logger'];
        $logger->error('I am NOT an error!!!');
        try {
            /**
             * @var PDO $pdo
             */
            $pdo = $dic['Yapeal.Database.Connection'];
            $sql = sprintf(
                'SELECT * FROM "%1$s"."%2$sutilEveApi" WHERE "isActive"=1 ORDER BY RAND()',
                $dic['Yapeal.Database.database'],
                $dic['Yapeal.Database.tablePrefix']
            );
            /**
             * @var PDOStatement $smt
             */
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                $mess = 'No active Eve APIs will exist now';
                $logger->error($mess);
                return 1;
            }
            foreach ($result as $record) {
                $class = 'Yapeal\\Database\\' . $record['section'] . '\\'
                    . $record['api'];
                if (class_exists($class)) {
                    print 'Yes the class exists was given ' . $class . PHP_EOL;
                } else {
                    print 'Class not found ' . $class . PHP_EOL;
                }
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
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.dirName'])) {
            $dic['Yapeal.dirName'] =
                str_replace('\\', '/', dirname(__DIR__)) . '/';
        }
        $this->wireErrorLogger($dic);
        $this->wireLogLogger($dic);
        $this->wireDatabase($dic);
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
    private function wireDatabase(ContainerInterface $dic)
    {
        if (isset($dic['Yapeal.Database.Connection'])) {
            return;
        }
        $dic['Yapeal.Database.Connection'] = function ($dic) {
            if (empty($dic['Yapeal.Database.platform'])) {
                $dic['Yapeal.Database.platform'] = 'mysql';
            }
            if ($dic['Yapeal.Database.platform'] != 'mysql') {
                $mess = 'Unknown platform was given '
                    . $dic['Yapeal.Database.platform'];
                throw new YapealDatabaseException($mess);
            }
            /**
             * @param $dic
             */
            function wireDatabaseSettings(&$dic)
            {
                if (empty($dic['Yapeal.Database.class'])) {
                    $dic['Yapeal.Database.class'] = 'PDO';
                }
                if (empty($dic['Yapeal.Database.hostName'])) {
                    $dic['Yapeal.Database.hostName'] = 'localhost';
                }
                if (empty($dic['Yapeal.Database.database'])) {
                    $dic['Yapeal.Database.database'] = 'yapeal';
                }
                if (empty($dic['Yapeal.Database.password'])) {
                    $dic['Yapeal.Database.password'] = 'secret';
                }
                if (empty($dic['Yapeal.Database.tablePrefix'])) {
                    $dic['Yapeal.Database.tablePrefix'] = '';
                }
                if (empty($dic['Yapeal.Database.userName'])) {
                    $dic['Yapeal.Database.userName'] = 'YapealUser';
                }
            }

            wireDatabaseSettings($dic);
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
        /**
         * @param $dic
         *
         * @throws \RuntimeException
         * @return ErrorHandler
         */
        $dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @param $dic
             */
            function wireErrorSettings(&$dic)
            {
                if (empty($dic['Yapeal.Error.class'])) {
                    $dic['Yapeal.Error.class'] = 'Monolog\\ErrorHandler';
                }
                if (empty($dic['Yapeal.Error.channel'])) {
                    $dic['Yapeal.Error.channel'] = 'php';
                }
                if (empty($dic['Yapeal.Error.dirName'])) {
                    $dic['Yapeal.Error.dirName'] =
                        $dic['Yapeal.dirName'] . 'log/';
                }
                if (empty($dic['Yapeal.Error.fileName'])) {
                    $dic['Yapeal.Error.fileName'] = 'yapeal.log';
                }
            }

            wireErrorSettings($dic);
            $loggerName = 'Monolog\\Logger';
            if (isset($dic['Yapeal.Error.loggerName'])) {
                $loggerName = $dic['Yapeal.Error.loggerName'];
            } elseif (isset($dic['Yapeal.Log.class'])) {
                $loggerName = $dic['Yapeal.Log.class'];
            }
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $loggerName($dic['Yapeal.Error.channel']);
            if (is_a($logger, 'Monolog\\Logger')) {
                $group = array();
                $threshold = 'Logger::CRITICAL';
                if (isset($dic['Yapeal.Error.threshold'])) {
                    $threshold = 'Logger::' . $dic['Yapeal.Error.threshold'];
                }
                /**
                 * @var Logger $logger
                 */
                if (PHP_SAPI == 'cli') {
                    $group[] = new StreamHandler('php://stderr', Logger::DEBUG);
                }
                $group[] = new StreamHandler(
                    $dic['Yapeal.Error.dirName']
                    . $dic['Yapeal.Error.fileName'],
                    Logger::DEBUG
                );
                $logger->pushHandler(
                    new FingersCrossedHandler(
                        new GroupHandler($group),
                        constant($threshold),
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
                    constant($threshold),
                    false
                );
                return $error;
            } else {
                $mess = 'Could NOT register error/exception handler';
                throw new \RuntimeException($mess);
            }
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
        $dic['Yapeal.Log.Logger'] = function ($dic) {
            if (empty($dic['Yapeal.Log.class'])) {
                $dic['Yapeal.Log.class'] = 'Monolog\\Logger';
            }
            if (empty($dic['Yapeal.Log.channel'])) {
                $dic['Yapeal.Log.channel'] = 'yapeal';
            }
            if (empty($dic['Yapeal.Log.dirName'])) {
                $dic['Yapeal.Log.dirName'] = $dic['Yapeal.dirName'] . 'log/';
            }
            if (empty($dic['Yapeal.Log.fileName'])) {
                $dic['Yapeal.Log.fileName'] = 'yapeal.log';
            }
            $threshold = 'Logger::WARNING';
            if (isset($dic['Yapeal.Log.threshold'])) {
                $threshold = 'Logger::' . $dic['Yapeal.Log.threshold'];
            }
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $dic['Yapeal.Log.class']($dic['Yapeal.Log.channel']);
            if (is_a($logger, 'Monolog\\Logger')) {
                $group = array();
                /**
                 * @var Logger $logger
                 */
                if (PHP_SAPI == 'cli') {
                    $group[] = new StreamHandler('php://stderr', Logger::DEBUG);
                }
                $group[] = new StreamHandler(
                    $dic['Yapeal.Log.dirName'] . $dic['Yapeal.Log.fileName'],
                    Logger::DEBUG
                );
                $logger->pushHandler(
                    new FingersCrossedHandler(
                        new GroupHandler($group), constant($threshold), 25
                    )
                );
            }
            return $logger;
        };
    }
}
