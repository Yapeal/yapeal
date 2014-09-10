<?php
/**
 * Contains AbstractCommonEveApi class.
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
namespace Yapeal\Database;

use DOMDocument;
use LogicException;
use PDO;
use PDOException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use tidy;
use XSLTProcessor;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class AbstractCommonEveApi
 */
abstract class AbstractCommonEveApi implements EveApiDatabaseInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait, EveApiToolsTrait;
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
     *
     * @throws LogicException
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->info(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        /**
         * @var EveApiReadWriteInterface $data
         */
        $data->setEveApiSectionName(strtolower($this->getSectionName()))
             ->setEveApiName($this->getApiName())
            ->setEveApiArguments([])
             ->setEveApiXml();
        if ($this->cacheNotExpired(
            $this->getApiName(),
            $this->getSectionName()
        )
        ) {
            return;
        }
        if (!$this->oneShot($data, $retrievers, $preservers, $interval)) {
            return;
        }
        $this->updateCachedUntil($data, $interval, '0');
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    ) {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve Eve Api data for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->debug($mess);
            return false;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'Data retrieved is invalid for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $preservers->preserveEveApi($data);
        // No need / way to preserve XML errors to the database with normal
        // preserveTo*.
        if ($this->isEveApiXmlError($data, $interval)) {
            return true;
        }
        $method = 'preserveTo' . $this->getApiName();
        return $this->$method($data->getEveApiXml());
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param string $ownerID
     *
     * @throws LogicException
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
     * @deprecated Need to switch to using trait instead.
     * @throws LogicException
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
     * @return string
     */
    abstract protected function getSectionName();
    /**
     * @deprecated Need to switch to using trait instead.
     * @throws LogicException
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
     * @throws LogicException
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
                ->warning($mess, ['exception' => $exc]);
            return false;
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param int                      $interval
     *
     * @throws LogicException
     * @return bool
     */
    protected function isEveApiXmlError(
        EveApiReadWriteInterface &$data,
        &$interval
    ) {
        if (strpos($data->getEveApiXml(), '<error') === false) {
            return false;
        }
        $simple = new SimpleXMLElement($data->getEveApiXml());
        if (!isset($simple->error)) {
            return false;
        }
        $code = (int)$simple->error['code'];
        $mess = sprintf(
            'Eve Error (%3$s): Received from API %1$s/%2$s - %4$s',
            strtolower($this->getSectionName()),
            $this->getApiName(),
            $code,
            (string)$simple->error
        );
        if (strpos($mess, 'retry after') !== false) {
            $this->getLogger()
                 ->warning($mess);
            $interval = strtotime(substr($mess, -19) . '+00:00') - time();
        } elseif ($code < 200) {
            $this->getLogger()
                 ->warning($mess);
        } elseif ($code > 199 && $code < 300) { // API key errors.
            $mess .= ' for keyID: ' . $data->getEveApiArgument('keyID');
            $this->getLogger()
                 ->error($mess);
            $interval = 86400;
        } elseif ($code > 500 && $code < 903) { // API server internal errors.
            $this->getLogger()
                 ->warning($mess);
            $interval = 300;
        } elseif ($code > 903
            && $code < 905
        ) { // Major application or Yapeal error.
            $this->getLogger()
                 ->alert($mess);
            $interval = 86400;
        } else {
            $this->getLogger()
                 ->warning($mess);
            $interval = 300;
        }
        return true;
    }
    /**
     * @param EveApiReadInterface $data
     *
     * @throws LogicException
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
     *
     * @throws LogicException
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
        if (!isset($simple->currentTime)) {
            return;
        }
        $dateTime = gmdate(
            'Y-m-d H:i:s',
            strtotime($simple->currentTime . '+00:00') + $interval
        );
        $row = [
            $this->getApiName(),
            $dateTime,
            $ownerID,
            $this->getSectionName()
        ];
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
     * @param EveApiReadWriteInterface $data
     *
     * @return self
     */
    protected function xsltTransform(EveApiReadWriteInterface &$data)
    {
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new  SimpleXMLElement($this->getXsl()));
        $config = [
            'indent' => true,
            'indent-spaces' => 2,
            'output-xml' => true,
            'input-xml' => true,
            'wrap' => '1000'
        ];
        // Tidy
        $tidy = new tidy();
        $data->setEveApiXml(
            $tidy->repairString(
                $xslt->transformToXml(
                    new SimpleXMLElement($data->getEveApiXml())
                ),
                $config,
                'utf8'
            )
        );
        return $this;
    }
    /**
     * @var string
     */
    protected $xsl = <<<'XSL'
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
     * @deprecated Need to switch to using trait instead.
     * @var DatabasePreserverInterface $attributesDatabasePreserver
     */
    private $attributesDatabasePreserver;
    /**
     * @deprecated Need to switch to using trait instead.
     * @var DatabasePreserverInterface $valuesDatabasePreserver
     */
    private $valuesDatabasePreserver;
}
