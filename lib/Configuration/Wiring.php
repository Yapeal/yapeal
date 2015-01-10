<?php
/**
 * Contains Wiring class.
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
namespace Yapeal\Configuration;

use ArrayAccess;
use DomainException;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use Monolog\ErrorHandler;
use Monolog\Logger;
use PDO;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\ContainerAwareEventDispatcherInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;

/**
 * Class Wiring
 */
class Wiring
{
    use FilePathNormalizerTrait;
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->dic = $dic;
    }
    /**
     * return self
     */
    public function wireCache()
    {
        return $this;
    }
    /**
     * @return self
     * @throws InvalidArgumentException
     * @throws YapealException
     */
    public function wireConfig()
    {
        $configFiles = [
            $this->getFpn()
                 ->normalizeFile(__DIR__ . '/yapeal_defaults.yaml'),
            $this->getFpn()
                 ->normalizeFile(
                     $this->dic['Yapeal.baseDir'] . 'config/yapeal.yaml'
                 )
        ];
        if (isset($this->dic['Yapeal.vendorParentDir'])) {
            $configFiles[]
                = $this->getFpn()
                       ->normalizeFile(
                           $this->dic['Yapeal.vendorParentDir']
                           . 'config/yapeal.yaml'
                       );
        }
        $settings = [];
        // Process each file in turn so any substitutions are done in a more
        // consistent way.
        foreach ($configFiles as $configFile) {
            if (!is_readable($configFile) || !is_file($configFile)) {
                continue;
            }
            $settings = $this->parserConfigFile($configFile, $settings);
        }
        if (empty($settings)) {
            return $this;
        }
        // Assure NOT overwriting already existing settings.
        foreach ($settings as $key => $value) {
            if (empty($this->dic[$key])) {
                $this->dic[$key] = $value;
            }
        }
        return $this;
    }
    /**
     * @throws YapealDatabaseException
     * @return self
     */
    public function wireDatabase()
    {
        if (empty($this->dic['Yapeal.Database.CommonQueries'])) {
            $this->dic['Yapeal.Database.CommonQueries'] = function ($dic) {
                return new $dic['Yapeal.Database.sharedSql'](
                    $dic['Yapeal.Database.database'],
                    $dic['Yapeal.Database.tablePrefix']
                );
            };
        }
        if (isset($this->dic['Yapeal.Database.Connection'])) {
            return $this;
        }
        if ($this->dic['Yapeal.Database.platform'] != 'mysql') {
            $mess = 'Unknown platform, was given '
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
             * @type PDO $database
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
            $database->exec('SET NAMES utf8 COLLATE utf8_unicode_ci');
            return $database;
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireError()
    {
        if (isset($this->dic['Yapeal.Error.Logger'])) {
            return $this;
        }
        $this->dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @type Logger $logger
             */
            $logger = new $dic['Yapeal.Error.class'](
                $dic['Yapeal.Error.channel']
            );
            $group = [];
            if (PHP_SAPI == 'cli') {
                $group[] = new $dic['Yapeal.Error.Handlers.stream'](
                    'php://stderr', 100
                );
            }
            $group[] = new $dic['Yapeal.Error.Handlers.stream'](
                $dic['Yapeal.Error.logDir']
                . $dic['Yapeal.Error.fileName'],
                100
            );
            $logger->pushHandler(
                new $dic['Yapeal.Error.Handlers.fingersCrossed'](
                    new $dic['Yapeal.Error.Handlers.group']($group),
                    (int)$dic['Yapeal.Error.threshold'],
                    (int)$dic['Yapeal.Error.bufferSize']
                )
            );
            /**
             * @type ErrorHandler $error
             */
            $error = $dic['Yapeal.Error.Handlers.error'];
            $error::register(
                $logger,
                [],
                (int)$dic['Yapeal.Error.threshold'],
                (int)$dic['Yapeal.Error.threshold']
            );
            return $error;
        };
        // Activate error logger now since it is needed to log any future fatal
        // errors or exceptions.
        $this->dic['Yapeal.Error.Logger'];
        return $this;
    }
    /**
     * @return self
     */
    public function wireEvent()
    {
        if (empty($this->dic['Yapeal.Event.EveApiEvent'])) {
            $this->dic['Yapeal.Event.EveApiEvent'] = function ($dic) {
                return new $dic['Yapeal.Event.Events.eveApi'];
            };
        }
        if (empty($this->dic['Yapeal.Event.LogEvent'])) {
            $this->dic['Yapeal.Event.LogEvent'] = function ($dic) {
                return new $dic['Yapeal.Event.Events.log'];
            };
        }
        if (empty($this->dic['Yapeal.Event.EventDispatcher'])) {
            $this->dic['Yapeal.Event.EventDispatcher'] = function ($dic) {
                return new $dic['Yapeal.Event.dispatcher'](
                    $dic,
                    $dic['Yapeal.Event.EveApiEvent'],
                    $dic['Yapeal.Event.LogEvent']
                );
            };
        }
        $dic = $this->dic;
        /**
         * @type ContainerAwareEventDispatcherInterface $dispatcher
         */
        $dispatcher = $dic['Yapeal.Event.EventDispatcher'];
        $internal = explode(',', $dic['Yapeal.Event.Subscribers.internal']);
        $method = 'injectCallable';
        foreach ($internal as $subscriber) {
            $subscriber = trim($subscriber);
            $serviceID = $subscriber::$method($dic);
            $dispatcher->addSubscriberService($serviceID, $subscriber);
        }
        return $this;
    }
    /**
     * @return self
     */
    public function wireLog()
    {
        return $this;
    }
    /**
     * @return self
     */
    public function wireNetwork()
    {
        if (isset($this->dic['Yapeal.Network.Client'])) {
            return $this;
        }
        $this->dic['Yapeal.Network.Client'] = function ($dic) {
            $appComment = $dic['Yapeal.Network.appComment'];
            $appName = $dic['Yapeal.Network.appName'];
            $appVersion = $dic['Yapeal.Network.appVersion'];
            if (empty($appName)) {
                $appComment = '';
                $appVersion = '';
            }
            $userAgent = trim(
                str_replace(
                    [
                        '{machineType}',
                        '{osName}',
                        '{osRelease}',
                        '{phpVersion}',
                        '{appComment}',
                        '{appName}',
                        '{appVersion}'
                    ],
                    [
                        php_uname('m'),
                        php_uname('s'),
                        php_uname('r'),
                        PHP_VERSION,
                        $appComment,
                        $appName,
                        $appVersion
                    ],
                    $dic['Yapeal.Network.userAgent']
                )
            );
            $userAgent = ltrim($userAgent, '/ ');
            $headers = [
                'Accept' => $dic['Yapeal.Network.Headers.Accept'],
                'Accept-Charset' => $dic['Yapeal.Network.Headers.Accept-Charset'],
                'Accept-Encoding' => $dic['Yapeal.Network.Headers.Accept-Encoding'],
                'Accept-Language' => $dic['Yapeal.Network.Headers.Accept-Language'],
                'Connection' => $dic['Yapeal.Network.Headers.Connection'],
                'Keep-Alive' => $dic['Yapeal.Network.Headers.Keep-Alive']
            ];
            // Clean up any extra spaces and EOL chars from Yaml.
            foreach ($headers as $key => $value) {
                $headers[$key] = trim(str_replace(' ', '', $value));
            }
            if (!empty($userAgent)) {
                $headers['User-Agent'] = $userAgent;
            }
            $defaults = [
                'headers' => $headers,
                'timeout' => (int)$dic['Yapeal.Network.timeout'],
                'connect_timeout' => (int)$dic['Yapeal.Network.connect_timeout'],
                'verify' => $dic['Yapeal.Network.verify'],
            ];
            return new $dic['Yapeal.Network.class'](
                [
                    'base_url' => $dic['Yapeal.Network.baseUrl'],
                    'defaults' => $defaults
                ]
            );
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireXml()
    {
        if (empty($this->dic['Yapeal.Xml.Data'])) {
            $this->dic['Yapeal.Xml.Data'] = $this->dic->factory(
                function ($dic) {
                    return new $dic['Yapeal.Xml.class']();
                }
            );
        }
        if (empty($this->dic['Yapeal.Xml.Preserver'])) {
            $this->dic['Yapeal.Xml.Preserver'] = function ($dic) {
                if ('none' != $dic['Yapeal.Cache.fileSystemMode']) {
                    return new $dic['Yapeal.Xml.Preservers.file'](
                        $dic['Yapeal.Log.Logger'],
                        $dic['Yapeal.Cache.cacheDir']
                    );
                }
                return new $dic['Yapeal.Xml.Preservers.null']();
            };
        }
//        if (empty($this->dic['Yapeal.Xml.Retriever'])) {
//            $this->dic['Yapeal.Xml.Retriever'] = function ($dic) {
//                $retrievers = [];
//                if ('none' != $dic['Yapeal.Cache.fileSystemMode']) {
//                    $retrievers[] = new $dic['Yapeal.Xml.Retrievers.file'](
//                        $dic['Yapeal.Log.Logger'],
//                        $dic['Yapeal.Cache.cacheDir']
//                    );
//                }
//                return new $dic['Yapeal.Xml.Retrievers.group'](
//                    $dic['Yapeal.Log.Logger'], $retrievers
//                );
//            };
//        }
        return $this;
    }
    /**
     * @param array|string $settings
     *
     * @return array|string
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function doSubs($settings)
    {
        if (empty($settings)) {
            return $settings;
        }
        if (!(is_string($settings) || is_array($settings))) {
            $mess = 'Settings MUST be a string or string array, but was given '
                    . getType($settings);
            throw new InvalidArgumentException($mess);
        }
        $depth = 0;
        $maxDepth = 10;
        $regEx = '/(?<all>\{(?<name>Yapeal(?:\.\w+)+)\})/';
        $dic = $this->dic;
        do {
            $settings = preg_replace_callback(
                $regEx,
                function ($match) use ($settings, $dic) {
                    if (isset($settings[$match['name']])) {
                        return $settings[$match['name']];
                    }
                    if (isset($dic[$match['name']])) {
                        return $dic[$match['name']];
                    }
                    return $match['all'];
                },
                $settings,
                -1,
                $count
            );
            if (++$depth > $maxDepth) {
                $mess
                    = 'Exceeded maximum depth, check for possible circular reference(s)';
                throw new DomainException($mess);
            }
            $lastError = preg_last_error();
            if (PREG_NO_ERROR != $lastError) {
                $lastError = array_flip(
                                 get_defined_constants(true)['pcre']
                             )[$lastError];
                $mess = 'Received preg error ' . $lastError;
                throw new DomainException($mess);
            }
        } while ($count > 0);
        return $settings;
    }
    /**
     * @param $configFile
     * @param $settings
     *
     * @return array|string
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws YapealException
     */
    protected function parserConfigFile($configFile, $settings)
    {
        try {
            $rItIt = new RecursiveIteratorIterator(
                new RecursiveArrayIterator(
                    (new Parser())->parse(
                        file_get_contents($configFile),
                        true,
                        false
                    )
                )
            );
        } catch (ParseException $exc) {
            $mess = sprintf(
                'Unable to parse the YAML configuration file %2$s.'
                . ' The error message was %1$s',
                $exc->getMessage(),
                $configFile
            );
            throw new YapealException($mess, 0, $exc);
        }
        foreach ($rItIt as $leafValue) {
            $keys = [];
            foreach (range(0, $rItIt->getDepth()) as $depth) {
                $keys[] = $rItIt->getSubIterator($depth)
                                ->key();
            }
            $settings[implode('.', $keys)] = $leafValue;
        }
        $settings = $this->doSubs($settings);
        return $settings;
    }
    /**
     * @type ContainerInterface|ArrayAccess $dic
     */
    protected $dic;
}
