<?php
/**
 * Contains EveApiDatabaseCache class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2013-2014  Michael Cummings
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
 * You should be able to find a copy of this license in the LICENSE file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2013-2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link      http://code.google.com/p/yapeal/
 * @link      http://www.eveonline.com/
 */
namespace Yapeal\Caching;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Yapeal\Database\DatabaseConnection;
use Yapeal\Database\QueryBuilder;

/**
 * EveApiDatabaseCache class.
 *
 * @package Yapeal\Caching
 */
class EveApiDatabaseCache implements EveApiCacheInterface, LoggerAwareInterface
{
    /**
     * @var string Name of the Eve API being cached.
     */
    private $api;
    /**
     * @var \ADOConnection
     */
    private $con;
    /**
     * @var string Holds SHA1 hash of $section, $api, $postParams.
     */
    private $hash = '';
    /**
     * @var LoggerInterface Holds instance of logger.
     */
    private $logger;
    /**
     * @var string The api section that $api belongs to.
     */
    private $section;
    /**
     * @param  string         $api
     * @param  string         $hash
     * @param  string         $section
     * @param \ADOConnection  $con
     * @param LoggerInterface $logger
     */
    public function __construct(
        $api,
        $hash,
        $section,
        LoggerInterface $logger = null,
        \ADOConnection $con = null
    ) {
        $this->setLogger($logger);
        $this->setApi($api);
        $this->setCon($con);
        $this->setHash($hash);
        $this->setSection($section);
    }
    /**
     * @param string         $xml Eve Api XML to be cached.
     * @param \ADOConnection $con
     *
     * @return self Returns self to allow fluid interface.
     */
    public function cacheXml($xml, \ADOConnection $con = null)
    {
        try {
            // Get a new query instance.
            $qb = new QueryBuilder(YAPEAL_TABLE_PREFIX
                . 'utilXmlCache', YAPEAL_DSN, false);
            $row = array(
                'api' => $this->api,
                'hash' => $this->hash,
                'section' => $this->section,
                'xml' => $xml
            );
            $qb->addRow($row);
            $qb->store();
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::WARNING, $e->getMessage());
            $mess = 'Could NOT cache XML to database';
            $this->logger->log(LogLevel::ERROR, $mess);
        }
        return $this;
    }
    /**
     * @param \ADOConnection $con
     *
     * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
     */
    public function deleteCachedXml(\ADOConnection $con = null)
    {
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
            $sql = 'delete from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
            $sql .= ' where';
            $sql .= ' `hash`=' . $con->qstr($this->hash);
            $con->Execute($sql);
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());
            $mess = 'Could NOT delete cached XML from database';
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param \ADOConnection $con
     *
     * @return string|false Returns XML if data is available, else returns FALSE.
     */
    public function getCachedXml(\ADOConnection $con = null)
    {
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
            $sql = 'select sql_no_cache `xml`';
            $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
            $sql .= ' where';
            $sql .= ' `hash`=' . $con->qstr($this->hash);
            $result = (string)$con->GetOne($sql);
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());
            $mess = 'Could NOT retrieve cached XML from database';
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        if (empty($result)) {
            $mess = 'Retrieved cached XML from database was empty';
            $this->logger->log(LogLevel::WARNING, $mess);
            return false;
        }
        return $result;
    }
    /**
     * @param string $api
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setApi($api)
    {
        $this->api = $api;
        return $this;
    }
    /**
     * @param \ADOConnection|null $con
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setCon(\ADOConnection $con = null)
    {
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::CRITICAL, $e->getMessage());
            $mess = 'Could NOT set database connection';
            $this->logger->log(LogLevel::CRITICAL, $mess);
        }
        $this->con = $con;
        return $this;
    }
    /**
     * @param string $hash
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface|null $logger
     *
     * @return null|void
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }
    /**
     * @param string $section
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }
}
