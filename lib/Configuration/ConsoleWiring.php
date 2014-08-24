<?php
/**
 * Contains ConsoleWiring class.
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
 */
namespace Yapeal\Configuration;

use Guzzle\Http\Client;
use Monolog\ErrorHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use Psr\Log\LoggerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Yapeal\Container\ContainerInterface;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Xml\FileCachePreserver;
use Yapeal\Xml\GuzzleNetworkRetriever;

/**
 * Class ConsoleWiring
 */
class ConsoleWiring
{
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->dic = $dic;
    }
    /**
     * @return self
     */
    public function wireCommonSqlQueries()
    {
        if (isset($this->dic['Yapeal.Database.CommonQueries'])) {
            return $this;
        }
        $this->dic['Yapeal.Database.CommonQueries'] = function ($dic) {
            return new CommonSqlQueries(
                $dic['Yapeal.Database.database'],
                $dic['Yapeal.Database.tablePrefix']
            );
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireConfiguration()
    {
        if (isset($this->dic['Yapeal.Config.Parser'])) {
            return $this;
        }
        $this->dic['Yapeal.Config.Parser'] = function ($dic) {
            $configFile = $dic['Yapeal.Config.configDir']
                . $dic['Yapeal.Config.fileName'];
            if (!is_readable($configFile) || !is_file($configFile)) {
                $mess = sprintf(
                    'Configuration file %1$s is NOT accessible',
                    $configFile
                );
                throw new YapealException($mess);
            }
            /**
             * @var Parser $parser
             */
            $parser = new $dic['Yapeal.Config.class'];
            $config = file_get_contents($configFile);
            try {
                $config =
                    $parser->parse($config, true, false);
            } catch (ParseException $exc) {
                $mess = sprintf(
                    'Unable to parse the YAML configuration file %2$s.'
                    . ' The error message was %1$s',
                    $exc->getMessage(),
                    $configFile
                );
                throw new YapealException($mess, 0, $exc);
            }
            $rItIt = new RecursiveIteratorIterator(
                new RecursiveArrayIterator($config)
            );
            foreach ($rItIt as $leafValue) {
                $keys = [];
                foreach (range(0, $rItIt->getDepth()) as $depth) {
                    $keys[] = $rItIt->getSubIterator($depth)
                                    ->key();
                }
                $dic[implode('.', $keys)] = str_replace(
                    ['{Yapeal.baseDir}', '{Yapeal.cwd}'],
                    [$dic['Yapeal.baseDir'], $dic['Yapeal.cwd']],
                    $leafValue
                );
            }
            return $parser;
        };
        return $this;
    }
    /**
     * @throws YapealDatabaseException
     * @return self
     */
    public function wireDatabase()
    {
        if (isset($this->dic['Yapeal.Database.Connection'])) {
            return $this;
        }
        if ($this->dic['Yapeal.Database.platform'] != 'mysql') {
            $mess = 'Unknown platform was given '
                . $this->dic['Yapeal.Database.platform'];
            throw new YapealDatabaseException($mess);
        }
        $this->dic['Yapeal.Database.Connection'] = function ($dic) {
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
            $database->exec("SET SESSION SQL_MODE='ANSI,TRADITIONAL'");
            $database->exec(
                'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE'
            );
            $database->exec("SET SESSION TIME_ZONE='+00:00'");
            $database->exec('SET NAMES UTF8');
            return $database;
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireDefaults()
    {
        $defaults = [
            'Yapeal.Config.class' => 'Symfony\\Component\\Yaml\\Parser',
            'Yapeal.Config.configDir' => $this->dic['Yapeal.baseDir']
                . 'config/',
            'Yapeal.Config.fileName' => 'yapeal.yaml',
            'Yapeal.Database.platform' => 'mysql',
            'Yapeal.Database.class' => 'PDO',
            'Yapeal.Database.hostName' => 'localhost',
            'Yapeal.Database.database' => 'yapeal',
            'Yapeal.Database.password' => 'secret',
            'Yapeal.Database.tablePrefix' => '',
            'Yapeal.Database.userName' => 'YapealUser',
            'Yapeal.Error.bufferSize' => 25,
            'Yapeal.Error.class' => 'Monolog\\ErrorHandler',
            'Yapeal.Error.channel' => 'php',
            'Yapeal.Error.fileName' => 'yapeal.log',
            'Yapeal.Error.logDir' => $this->dic['Yapeal.baseDir'] . 'log/',
            'Yapeal.Error.threshold' => 500,
            'Yapeal.Log.bufferSize' => 25,
            'Yapeal.Log.class' => 'Monolog\\Logger',
            'Yapeal.Log.channel' => 'yapeal',
            'Yapeal.Log.logDir' => $this->dic['Yapeal.baseDir'] . 'log/',
            'Yapeal.Log.fileName' => 'yapeal.log',
            'Yapeal.Log.threshold' => 300,
            'Yapeal.Network.baseUrl' => 'https://api.eveonline.com'
        ];
        foreach ($defaults as $setting => $default) {
            if (empty($this->dic[$setting])) {
                $this->dic[$setting] = $default;
            }
        }
        return $this;
    }
    /**
     * @return self
     */
    public function wireErrorLogger()
    {
        if (isset($this->dic['Yapeal.Error.Logger'])) {
            return $this;
        }
        if (empty($this->dic['Yapeal.Error.loggerName'])) {
            $loggerName = 'Monolog\\Logger';
            if (isset($this->dic['Yapeal.Log.class'])) {
                $loggerName = $this->dic['Yapeal.Log.class'];
            }
            $this->dic['Yapeal.Error.loggerName'] = $loggerName;
        }
        /**
         * @param $dic
         *
         * @throws \RuntimeException
         * @return ErrorHandler
         */
        $this->dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $dic['Yapeal.Error.loggerName'](
                $dic['Yapeal.Error.channel']
            );
            $group = [];
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
                    $dic['Yapeal.Error.bufferSize']
                )
            );
            /**
             * @var ErrorHandler $error
             */
            $error = $dic['Yapeal.Error.class'];
            $error::register(
                $logger,
                [],
                $dic['Yapeal.Error.threshold'],
                $dic['Yapeal.Error.threshold']
            );
            return $error;
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireLogLogger()
    {
        if (isset($this->dic['Yapeal.Log.Logger'])) {
            return $this;
        }
        $this->dic['Yapeal.Log.Logger'] = function ($dic) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = new $dic['Yapeal.Log.class']($dic['Yapeal.Log.channel']);
            $group = [];
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
                    new GroupHandler($group),
                    $dic['Yapeal.Log.threshold'],
                    $dic['Yapeal.Log.bufferSize']
                )
            );
            return $logger;
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wirePreserver()
    {
        if (isset($this->dic['Yapeal.Xml.Preserver'])) {
            return $this;
        }
        $this->dic['Yapeal.Xml.Preserver'] = function ($dic) {
            return new FileCachePreserver(
                $dic['Yapeal.Log.Logger'],
                $dic['Yapeal.baseDir'] . 'cache/'
            );
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireRetriever()
    {
        if (isset($this->dic['Yapeal.Xml.Retriever'])) {
            return $this;
        }
        $this->dic['Yapeal.Xml.Retriever'] = function ($dic) {
            $defaults = [
                'headers' => [
                    'Accept' =>
                        'text/xml,application/xml,application/xhtml+xml;'
                        . 'q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;'
                        . 'q=0.6,*/*;q=0.5',
                    'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
                    'Accept-Encoding' => 'gzip',
                    'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
                    'Connection' => 'Keep-Alive',
                    'Keep-Alive' => '300'
                ],
                'timeout' => 10,
                'connect_timeout' => 30,
                'verify' => $dic['Yapeal.baseDir'] . 'config/eveonline.crt',
            ];
            return new GuzzleNetworkRetriever(
                $dic['Yapeal.Log.Logger'],
                new Client(
                    $dic['Yapeal.Network.baseUrl'],
                    ['defaults' => $defaults]
                )
            );
        };
        return $this;
    }
    /**
     * @var ContainerInterface $dic
     */
    protected $dic;
}
