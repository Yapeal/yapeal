<?php
/**
 * Contains APIKeyInfo class.
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
namespace Yapeal\Database\Account;

use PDO;
use PDOStatement;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo implements LoggerAwareInterface
{
    /**
     * @param PDO              $pdo
     * @param LoggerInterface  $logger
     * @param CommonSqlQueries $csq
     */
    public function __construct(
        PDO $pdo,
        LoggerInterface $logger,
        CommonSqlQueries $csq
    ) {
        $this->setPdo($pdo);
        $this->setLogger($logger);
        $this->setSectionName(
            strtolower(basename(str_replace('\\', '/', __DIR__)))
        );
        $this->setApiName(basename(str_replace('\\', '/', __CLASS__)));
        $this->csq = $csq;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retriever
     * @param EveApiPreserverInterface $preserver
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retriever,
        EveApiPreserverInterface $preserver
    ) {
        $this->getLogger()
             ->info(
                 'Starting autoMagic for ' . $this->getSectionName() . '\\'
                 . $this->getApiName()
             );
        /**
         * @var PDO $pdo
         */
        $pdo = $this->getPdo();
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $this->getCsq();
        $sql = $csq->getActiveRegisteredKeys();
        $this->getLogger()
             ->debug($sql);
        /**
         * @var PDOStatement $smt
         */
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->getLogger()
                 ->notice('No active registered keys');
            return;
        }
        /**
         * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
         */
        $data->setEveApiSectionName($this->getSectionName())
             ->setEveApiName($this->getApiName());
        foreach ($result as $key) {
            $data->setEveApiArguments($key)
                 ->setEveApiXml();
            $retriever->retrieveEveApi($data);
            if ($data->getEveApiXml() === false) {
                $mess =
                    'Could NOT retrieve Eve Api data for registered key '
                    . $key['keyID'];
                $this->getLogger()
                     ->debug($mess);
                continue;
            }
            if ($this->isInvalid($data)) {
                $mess = 'Data retrieved is invalid for registered key '
                    . $key['keyID'];
                $this->getLogger()
                     ->warning($mess);
                $data->setEveApiName('Invalid' . $this->getApiName());
                $preserver->preserveEveApi($data);
                continue;
            }
        }
        print 'I ran!!!' . PHP_EOL;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setApiName($value)
    {
        $this->apiName = $value;
        return $this;
    }
    /**
     * @param CommonSqlQueries $value
     *
     * @return self
     */
    public function setCsq($value)
    {
        $this->csq = $value;
        return $this;
    }
    /**
     * @param LoggerInterface $value
     *
     * @return self
     */
    public function setLogger(LoggerInterface $value)
    {
        $this->logger = $value;
        return $this;
    }
    /**
     * @param PDO $value
     *
     * @return self
     */
    public function setPdo(PDO $value)
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setSectionName($value)
    {
        $this->sectionName = $value;
        return $this;
    }
    /**
     * @var string
     */
    protected $apiName;
    /**
     * @var CommonSqlQueries
     */
    protected $csq;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var string
     */
    protected $sectionName;
    /**
     * @return string
     */
    protected function getApiName()
    {
        return $this->apiName;
    }
    /**
     * @return CommonSqlQueries
     */
    protected function getCsq()
    {
        return $this->csq;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    /**
     * @return PDO
     */
    protected function getPdo()
    {
        return $this->pdo;
    }
    /**
     * @return string
     */
    protected function getSectionName()
    {
        return $this->sectionName;
    }
    /**
     * @param EveApiReadInterface $data
     *
     * @return bool
     */
    protected function isInvalid(EveApiReadInterface $data)
    {
        //TODO Finish XSD validator
        $data->getEveApiXml();
        return true;
    }
}
