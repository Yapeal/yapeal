<?php
/**
 * Contains AbstractCommonEveApi class.
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
namespace Yapeal\Database;

use DOMDocument;
use PDO;
use PDOException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use XSLTProcessor;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class AbstractCommonEveApi
 */
abstract class AbstractCommonEveApi implements LoggerAwareInterface
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
        $this->setCsq($csq);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     */
    abstract public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    );
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
     * @var string
     */
    protected $xsl = <<<XSL
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
        version="1.0"
        encoding="utf-8"
        omit-xml-declaration="no"
        standalone="no"
        indent="yes"/>
    <xsl:template match="rowset">
        <xsl:choose>
            <xsl:when test="@name">
                <xsl:element name="{@name}">
                    <xsl:copy-of select="@key"/>
                    <xsl:copy-of select="@columns"/>
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="."/>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
</xsl:transform>
XSL;
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param string $ownerID
     *
     * @return bool
     */
    protected function cacheNotExpired($apiName, $sectionName, $ownerID = '0')
    {
        $mess = sprintf(
            'Checking if cache expired on table %1$s%2$s for ownerID = %3$s',
            strtolower($sectionName),
            $apiName,
            $ownerID
        );
        $this->getLogger()
             ->debug($mess);
        $sql = $this->getCsq()
                    ->getUtilCachedUntilExpires(
                        $apiName,
                        $sectionName,
                        $ownerID
                    );
        $this->getLogger()
             ->debug($sql);
        $stmt = $this->getPdo()
                     ->query($sql);
        $expires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($expires)) {
            $this->getLogger()
                 ->debug('No UtilCachedUntil record found');
            return false;
        }
        if (strtotime($expires[0]['expires'] . '+00:00') < time()) {
            $this->getLogger()
                 ->debug('Expired UtilCachedUntil record found');
            return false;
        }
        return true;
    }
    /**
     * @return string
     */
    abstract protected function getApiName();
    /**
     * @return DatabasePreserverInterface
     */
    protected function getAttributesDatabasePreserver()
    {
        if (empty($this->attributesDatabasePreserver)) {
            $this->attributesDatabasePreserver =
                new AttributesDatabasePreserver(
                    $this->getPdo(),
                    $this->getLogger(),
                    $this->getCsq()
                );
        }
        return $this->attributesDatabasePreserver;
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
    abstract protected function getSectionName();
    /**
     * @return DatabasePreserverInterface
     */
    protected function getValuesDatabasePreserver()
    {
        if (empty($this->valuesDatabasePreserver)) {
            $this->valuesDatabasePreserver = new ValuesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        return $this->valuesDatabasePreserver;
    }
    /**
     * @return string
     */
    protected function getXsl()
    {
        return $this->xsl;
    }
    /**
     * @param EveApiReadInterface $data
     *
     * @return bool
     */
    protected function gotApiLock(EveApiReadInterface &$data)
    {
        $sql = $this->getCsq()
                    ->getApiLock($data->getHash());
        $this->getLogger()
             ->info($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Could NOT get lock for %1$s/%2$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return false;
        }
    }
    /**
     * @param EveApiReadInterface $data
     *
     * @return bool
     */
    protected function isInvalid(EveApiReadInterface &$data)
    {
        $this->getLogger()
             ->debug('Started XSD validating');
        $oldErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument();
        $dom->loadXML($data->getEveApiXml());
        $schema = sprintf(
            str_replace('\\', '/', __DIR__) . '/%1$s/%2$s.xsd',
            ucfirst($data->getEveApiSectionName()),
            $data->getEveApiName()
        );
        if ($dom->schemaValidate($schema)) {
            libxml_use_internal_errors($oldErrors);
            return false;
        }
        $logger = $this->getLogger();
        foreach (libxml_get_errors() as $error) {
            $logger->debug($error->message);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($oldErrors);
        return true;
    }
    /**
     * @param EveApiReadInterface $data
     * @param int                 $interval
     * @param string              $ownerID
     */
    protected function updateCachedUntil(
        EveApiReadInterface $data,
        $interval,
        $ownerID
    ) {
        $simple = new SimpleXMLElement($data->getEveApiXml());
        $sql = $this->getCsq()
                    ->getUtilCachedUntilUpsert();
        $pdo = $this->getPdo();
        $dateTime = gmdate(
            'Y-m-d H:i:s',
            strtotime($simple->currentTime . '+00:00') + $interval
        );
        $row = array(
            $this->getApiName(),
            $dateTime,
            $ownerID,
            $this->getSectionName()
        );
        try {
            $pdo->beginTransaction();
            $statement = $pdo->prepare($sql);
            $statement->execute($row);
            $pdo->commit();
        } catch (PDOException $exc) {
            $pdo->rollBack();
        }
    }
    /**
     * @param EveApiXmlModifyInterface $data
     *
     * @return self
     */
    protected function xsltTransform(EveApiXmlModifyInterface &$data)
    {
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new  SimpleXMLElement($this->getXsl()));
        $data->setEveApiXml(
            $xslt->transformToXml(new SimpleXMLElement($data->getEveApiXml()))
        );
        return $this;
    }
    /**
     * @var DatabasePreserverInterface $attributesDatabasePreserver
     */
    private $attributesDatabasePreserver;
    /**
     * @var DatabasePreserverInterface $valuesDatabasePreserver
     */
    private $valuesDatabasePreserver;
}
