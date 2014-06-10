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

use DOMDocument;
use PDO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use XSLTProcessor;
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
            basename(str_replace('\\', '/', __DIR__))
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
        $pdo = $this->getPdo();
        $csq = $this->getCsq();
        $sql = $csq->getActiveRegisteredKeys();
        $this->getLogger()
             ->debug($sql);
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->getLogger()
                ->info('No active registered keys');
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
            $this->transformRowset($data);
            if ($this->isInvalid($data)) {
                $mess = 'Data retrieved is invalid for registered key '
                    . $key['keyID'];
                $this->getLogger()
                     ->warning($mess);
                $data->setEveApiName('Invalid' . $this->getApiName());
                $preserver->preserveEveApi($data);
                continue;
            }
            $preserver->preserveEveApi($data);
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
    protected function isInvalid(EveApiReadInterface &$data)
    {
        $this->getLogger()
             ->debug('Started XSD validating');
        $oldErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($data->getEveApiXml());
        $schema = str_replace('\\', '/', __DIR__) . '/' . $this->getApiName()
            . '.xsd';
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
     * @param EveApiXmlModifyInterface $data
     *
     * @return self
     */
    protected function transformRowset(EveApiXmlModifyInterface &$data)
    {
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new  SimpleXMLElement($this->getXsl()));
        $data->setEveApiXml(
            $xslt->transformToXml(new SimpleXMLElement($data->getEveApiXml()))
        );
        return $this;
    }
}
