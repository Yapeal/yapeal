<?php
/**
 * Contains Transformer class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsl;

use DOMDocument;
use SimpleXMLElement;
use tidy;
use XSLTProcessor;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;

/**
 * Class Transformer
 */
class Transformer implements TransformerInterface
{
    use EveApiEventEmitterTrait, RelativeFileSearchTrait;
    /**
     * Transformer Constructor.
     *
     * @param string|null $xslDir
     */
    public function __construct($xslDir = null)
    {
        $this->setXslDir($xslDir);
    }
    /**
     * Getter for $xslDir.
     *
     * @return string
     */
    public function getXslDir()
    {
        return $this->xslDir;
    }
    /**
     * Fluent interface setter for $xslDir.
     *
     * @param string $xslDir
     *
     * @return self Fluent interface.
     */
    public function setXslDir($xslDir)
    {
        $this->xslDir = $xslDir;
        return $this;
    }
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function transformEveApi(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $this->setYem($yem);
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event of %2$s/%3$s in %4$s',
            $eventName,
            ucfirst($data->getEveApiSectionName()),
            $data->getEveApiName(),
            __CLASS__
        );
        if ($data->hasEveApiArgument('keyID')) {
            $mess .= ' for keyID = ' . $data->getEveApiArgument('keyID');
        }
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $fileName = $this->setRelativeBaseDir(__DIR__)
                         ->findEveApiFile($data->getEveApiSectionName(), $data->getEveApiName(), 'xsl');
        if ('' === $fileName) {
            return $event;
        }
        $xml = $this->performTransform($fileName, $data->getEveApiXml());
        if (false === $xml) {
            $mess = sprintf(
                'Failed to transform xml of %1$s/%2$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            if ($data->hasEveApiArgument('keyID')) {
                $mess .= ' for keyID = ' . $data->getEveApiArgument('keyID');
            }
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return $event;
        }
        $xml = (new tidy())->repairString($xml, $this->tidyConfig, 'utf8');
        file_put_contents(dirname(dirname(str_replace('\\', '/', __DIR__))) . '/cache/test.xml', $xml);
        return $event->setData($data->setEveApiXml($xml))
                     ->eventHandled();
    }
    /**
     * @param string $fileName
     * @param string $xml
     *
     * @return string|false
     * @throws \LogicException
     */
    protected function performTransform($fileName, $xml)
    {
        $xslt = new XSLTProcessor();
        $oldErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument();
        $dom->load($fileName);
        $xslt->importStylesheet($dom);
        $xml = $xslt->transformToXml(new SimpleXMLElement($xml));
        if (false === $xml) {
            foreach (libxml_get_errors() as $error) {
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $error->message);
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($oldErrors);
        return $xml;
    }
    /**
     * Holds tidy config settings.
     *
     * @type array $tidyConfig
     */
    protected $tidyConfig = [
        'indent'        => true,
        'indent-spaces' => 4,
        'output-xml'    => true,
        'input-xml'     => true,
        'wrap'          => '1000'
    ];
    /**
     * Holds base directory where Eve API XSL files can be found.
     *
     * @type string $xslDir
     */
    protected $xslDir;
}
