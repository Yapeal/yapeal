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
use FilesystemIterator;
use InvalidArgumentException;
use Monolog\ErrorHandler;
use Monolog\Logger;
use PDO;
use RecursiveArrayIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Traversable;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Network\GuzzleNetworkRetriever;

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
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function wireAll()
    {
        $this->wireConfig()
             ->wireError()
             ->wireEvent()
             ->wireLog()
             ->wireXml()
             ->wireXsl()
             ->wireCache()
             ->wireNetwork()
             ->wireDatabase()
             ->wireEveApi();
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
        if (!(is_string($settings) || is_array($settings))) {
            $mess = 'Settings MUST be a string or string array, but was given ' . gettype($settings);
            throw new InvalidArgumentException($mess);
        }
        if ((is_string($settings) && '' === $settings)
            || (is_array(
                $settings
                && 0 === count($settings)
            ))
        ) {
            return $settings;
        }
        $depth = 0;
        $maxDepth = 10;
        $regEx = '/(?<all>\{(?<name>Yapeal(?:\.\w+)+)\})/';
        $dic = $this->dic;
        do {
            $settings = preg_replace_callback(
                $regEx,
                function ($match) use ($settings, $dic) {
                    if (!empty($settings[$match['name']])) {
                        return $settings[$match['name']];
                    }
                    if (!empty($dic[$match['name']])) {
                        return $dic[$match['name']];
                    }
                    return $match['all'];
                },
                $settings,
                -1,
                $count
            );
            if (++$depth > $maxDepth) {
                $mess = 'Exceeded maximum depth, check for possible circular reference(s)';
                throw new DomainException($mess);
            }
            $lastError = preg_last_error();
            if (PREG_NO_ERROR !== $lastError) {
                $constants = array_flip(get_defined_constants(true)['pcre']);
                $lastError = $constants[$lastError];
                $mess = 'Received preg error ' . $lastError;
                throw new DomainException($mess);
            }
        } while ($count > 0);
        return $settings;
    }
    /**
     * @return array
     */
    protected function getFilteredEveApiSubscriberList()
    {
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO
                 | FilesystemIterator::KEY_AS_PATHNAME
                 | FilesystemIterator::SKIP_DOTS
                 | FilesystemIterator::UNIX_PATHS;
        $rdi = new RecursiveDirectoryIterator(
            $this->dic['Yapeal.baseDir'] . '/lib/EveApi/'
        );
        $rdi->setFlags($flags);
        $rcfi = new RecursiveCallbackFilterIterator(
            $rdi, function ($current, $key, $iterator) {
            /**
             * @type \RecursiveDirectoryIterator $iterator
             */
            if ($iterator->hasChildren()) {
                return true;
            }
            $dirs = [
                'Account',
                'Api',
                'Char',
                'Corp',
                'Eve',
                'Map',
                'Map',
                'Server'
            ];
            /**
             * @type \SplFileInfo $current
             */
            return (in_array(basename(dirname($key)), $dirs, true)
                    && $current->isFile()
                    && 'php' === $current->getExtension());
        }
        );
        $rii = new RecursiveIteratorIterator(
            $rcfi, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        $rii->setMaxDepth(3);
        $fpn = $this->getFpn();
        $files = [];
        foreach ($rii as $file) {
            $files[] = $fpn->normalizeFile($file->getPathname());
        }
        return $files;
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
            /**
             * @type RecursiveIteratorIterator|Traversable $rItIt
             */
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
                'Unable to parse the YAML configuration file %2$s.' . ' The error message was %1$s',
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
     * @return self Fluent interface.
     */
    protected function wireCache()
    {
        return $this;
    }
    /**
     * @return self Fluent interface.
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws YapealException
     */
    protected function wireConfig()
    {
        $dic = $this->dic;
        $fpn = $this->getFpn();
        $path = $fpn->normalizePath(dirname(dirname(__DIR__)));
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
        $configFiles = [
            $fpn->normalizeFile(__DIR__ . '/yapeal_defaults.yaml'),
            $fpn->normalizeFile($dic['Yapeal.baseDir'] . 'config/yapeal.yaml')
        ];
        if (!empty($this->dic['Yapeal.vendorParentDir'])) {
            $configFiles[] = $fpn->normalizeFile($dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml');
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
        if (0 === count($settings)) {
            return $this;
        }
        // Assure NOT overwriting already existing settings.
        foreach ($settings as $key => $value) {
            if (empty($dic[$key])) {
                $dic[$key] = $value;
            }
        }
        return $this;
    }
    /**
     * @return self Fluent interface.
     * @throws YapealDatabaseException
     */
    protected function wireDatabase()
    {
        if (empty($this->dic['Yapeal.Database.CommonQueries'])) {
            $this->dic['Yapeal.Database.CommonQueries'] = function ($dic) {
                return new $dic['Yapeal.Database.sharedSql'](
                    $dic['Yapeal.Database.database'], $dic['Yapeal.Database.tablePrefix']
                );
            };
        }
        if (!empty($this->dic['Yapeal.Database.Connection'])) {
            return $this;
        }
        if ('mysql' !== $this->dic['Yapeal.Database.platform']) {
            $mess = 'Unknown platform, was given ' . $this->dic['Yapeal.Database.platform'];
            throw new YapealDatabaseException($mess);
        }
        $this->dic['Yapeal.Database.Connection'] = function ($dic) {
            $dsn = $dic['Yapeal.Database.platform'] . ':host=' . $dic['Yapeal.Database.hostName'] . ';charset=utf8';
            if (!empty($dic['Yapeal.Database.port'])) {
                $dsn .= ';port=' . $dic['Yapeal.Database.port'];
            }
            /**
             * @type PDO $database
             */
            $database = new $dic['Yapeal.Database.class'](
                $dsn, $dic['Yapeal.Database.userName'], $dic['Yapeal.Database.password']
            );
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec('SET SESSION SQL_MODE=\'ANSI,TRADITIONAL\'');
            $database->exec(
                'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE'
            );
            $database->exec('SET SESSION TIME_ZONE=\'+00:00\'');
            $database->exec('SET NAMES utf8 COLLATE utf8_unicode_ci');
            return $database;
        };
        return $this;
    }
    /**
     * @return self Fluent interface.
     */
    protected function wireError()
    {
        if (!empty($this->dic['Yapeal.Error.Logger'])) {
            return $this;
        }
        $this->dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @type Logger $logger
             */
            $logger = new $dic['Yapeal.Error.class']($dic['Yapeal.Error.channel']);
            $group = [];
            if ('cli' === PHP_SAPI) {
                $group[] = new $dic['Yapeal.Error.Handlers.stream'](
                    'php://stderr', 100
                );
            }
            $group[] = new $dic['Yapeal.Error.Handlers.stream'](
                $dic['Yapeal.Error.logDir'] . $dic['Yapeal.Error.fileName'], 100
            );
            $logger->pushHandler(
                new $dic['Yapeal.Error.Handlers.fingersCrossed'](
                    new $dic['Yapeal.Error.Handlers.group'](
                        $group
                    ), (int)$dic['Yapeal.Error.threshold'], (int)$dic['Yapeal.Error.bufferSize']
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
     * @return self Fluent interface.
     */
    protected function wireEveApi()
    {
        /**
         * @type ContainerInterface|\ArrayObject $dic
         */
        $dic = $this->dic;
        /**
         * @type \Yapeal\Event\EventMediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.EventMediator'];
        $internal = $this->getFilteredEveApiSubscriberList();
        if (0 !== count($internal)) {
            $base = 'Yapeal.EveApi';
            /**
             * @type \SplFileInfo $subscriber
             */
            foreach ($internal as $subscriber) {
                $service = sprintf(
                    '%1$s.%2$s.%3$s',
                    $base,
                    basename(dirname($subscriber)),
                    basename($subscriber, '.php')
                );
                if (!array_key_exists($service, $dic)) {
                    $dic[$service] = function () use ($dic, $service, $mediator) {
                        $class = '\\' . str_replace('.', '\\', $service);
                        /**
                         * @type \Yapeal\EveApi\AbstractCommonEveApi $callable
                         */
                        $callable = new $class();
                        return $callable->setCsq(
                            $dic['Yapeal.Database.CommonQueries']
                        )
                                        ->setPdo($dic['Yapeal.Database.Connection']);
                    };
                }
                $events = [
                    $service . '.start' => [
                        'eveApiStart',
                        'last'
                    ]
                ];
                if (false === strpos($subscriber, 'Section')) {
                    $events[$service . '.preserve'] = ['eveApiPreserve', 'last'];
                }
                $mediator->addServiceSubscriberByEventList(
                    $service,
                    $events
                );
            }
        }
        return $this;
    }
    /**
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    protected function wireEvent()
    {
        $dic = $this->dic;
        if (empty($dic['Yapeal.Event.EveApiEvent'])) {
            $dic['Yapeal.Event.EveApi'] = $dic->factory(
                function ($dic) {
                    return new $dic['Yapeal.Event.Factories.eveApi']();
                }
            );
        }
        if (empty($this->dic['Yapeal.Event.LogEvent'])) {
            $this->dic['Yapeal.Event.LogEvent'] = $this->dic->factory(
                function ($dic) {
                    return new $dic['Yapeal.Event.Factories.log'];
                }
            );
        }
        if (empty($dic['Yapeal.Event.EventMediator'])) {
            $dic['Yapeal.Event.EventMediator'] = function ($dic) {
                return new $dic['Yapeal.Event.mediator']($dic);
            };
        }
        return $this;
    }
    /**
     * @return self Fluent interface.
     */
    protected function wireLog()
    {
        $dic = $this->dic;
        $class = $dic['Yapeal.Log.class'];
        if (empty($dic['Yapeal.Log.Logger'])) {
            $dic['Yapeal.Log.Logger'] = function () use ($dic, $class) {
                $group = [];
                if (PHP_SAPI === 'cli') {
                    $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                        'php://stderr', 100
                    );
                }
                $group[] = new $dic['Yapeal.Log.Handlers.stream'](
                    $dic['Yapeal.Log.logDir'] . $dic['Yapeal.Log.fileName'], 100
                );
                return new $class(
                    $dic['Yapeal.Log.channel'], [
                        new $dic['Yapeal.Log.Handlers.fingersCrossed'](
                            new $dic['Yapeal.Log.Handlers.group'](
                                $group
                            ), (int)$dic['Yapeal.Log.threshold'], (int)$dic['Yapeal.Log.bufferSize']
                        )
                    ]
                );
            };
        }
        /**
         * @type \Yapeal\Event\EventMediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.EventMediator'];
        $mediator->addServiceSubscriberByEventList(
            'Yapeal.Log.Logger',
            ['Yapeal.Log.log' => ['logEvent', 'last']]
        );
        return $this;
    }
    /**
     * @return self Fluent interface.
     */
    protected function wireNetwork()
    {
        $dic = $this->dic;
        if (empty($dic['Yapeal.Network.Client'])) {
            $dic['Yapeal.Network.Client'] = function ($dic) {
                $appComment = $dic['Yapeal.Network.appComment'];
                $appName = $dic['Yapeal.Network.appName'];
                $appVersion = $dic['Yapeal.Network.appVersion'];
                if ('' === $appName) {
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
                    'Accept'          => $dic['Yapeal.Network.Headers.Accept'],
                    'Accept-Charset'  => $dic['Yapeal.Network.Headers.Accept-Charset'],
                    'Accept-Encoding' => $dic['Yapeal.Network.Headers.Accept-Encoding'],
                    'Accept-Language' => $dic['Yapeal.Network.Headers.Accept-Language'],
                    'Connection'      => $dic['Yapeal.Network.Headers.Connection'],
                    'Keep-Alive'      => $dic['Yapeal.Network.Headers.Keep-Alive']
                ];
                // Clean up any extra spaces and EOL chars from Yaml.
                foreach ($headers as &$value) {
                    /** @noinspection ReferenceMismatchInspection */
                    $value = trim(str_replace(' ', '', $value));
                }
                unset($value);
                if ('' !== $userAgent) {
                    $headers['User-Agent'] = $userAgent;
                }
                $defaults = [
                    'base_uri'        => $dic['Yapeal.Network.baseUrl'],
                    'connect_timeout' => (int)$dic['Yapeal.Network.connect_timeout'],
                    'headers'         => $headers,
                    'timeout'         => (int)$dic['Yapeal.Network.timeout'],
                    'verify'          => $dic['Yapeal.Network.verify']
                ];
                return new $dic['Yapeal.Network.class']($defaults);
            };
        }
        if (empty($dic['Yapeal.Network.Retriever'])) {
            $dic['Yapeal.Network.Retriever'] = function ($dic) {
                return new GuzzleNetworkRetriever($dic['Yapeal.Network.Client']);
            };
        }
        /**
         * @type \Yapeal\Event\EventMediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.EventMediator'];
        $mediator->addServiceSubscriberByEventList(
            'Yapeal.Network.Retriever',
            ['Yapeal.EveApi.retrieve' => ['retrieveEveApi', 'last']]
        );
        return $this;
    }
    /**
     * Wire Xml section.
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    protected function wireXml()
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
                if ('none' !== $dic['Yapeal.Cache.fileSystemMode']) {
                    return new $dic['Yapeal.Xml.Preservers.file'](
                        $dic['Yapeal.Log.Logger'], $dic['Yapeal.Cache.cacheDir']
                    );
                }
                return new $dic['Yapeal.Xml.Preservers.null']();
            };
        }
        return $this;
    }
    /**
     * Wire Xsl section.
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    protected function wireXsl()
    {
        $dic = $this->dic;
        if (empty($dic['Yapeal.Xsl.Transformer'])) {
            $dic['Yapeal.Xsl.Transformer'] = $dic->factory(
                function ($dic) {
                    return new $dic['Yapeal.Xsl.class']($dic['Yapeal.Xsl.xslDir']);
                }
            );
        }
        /**
         * @type \Yapeal\Event\EventMediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.EventMediator'];
        $mediator->addServiceSubscriberByEventList(
            'Yapeal.Xsl.Transformer',
            ['Yapeal.EveApi.transform' => ['transformEveApi', 'last']]
        );
        return $this;
    }
    /**
     * @type ContainerInterface|ArrayAccess $dic
     */
    protected $dic;
}
