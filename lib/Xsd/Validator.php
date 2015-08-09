<?php
/**
 * Contains Validator class.
 *
 * PHP version 5.5
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
namespace Yapeal\Xsd;

use DOMDocument;
use SimpleXMLElement;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Validator
 */
class Validator
{
    use EveApiEventEmitterTrait, RelativeFileSearchTrait;
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
    public function validateEveApi(
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
                         ->findEveApiFile($data->getEveApiSectionName(), $data->getEveApiName(), 'xsd');
        if ('' === $fileName) {
            return $event;
        }
        $oldErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument();
        $dom->loadXML($data->getEveApiXml());
        if (!$dom->schemaValidate($fileName)) {
            foreach (libxml_get_errors() as $error) {
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $error->message);
            }
            libxml_clear_errors();
            libxml_use_internal_errors($oldErrors);
            return $event;
        }
        libxml_clear_errors();
        libxml_use_internal_errors($oldErrors);
        if (false !== strpos($data->getEveApiXml(), '<error')) {
            $data = $this->processEveApiXmlError($data, $yem);
            $event->setData($data);
            $this->emitEvents($data, 'xmlError');
        }
        return $event->eventHandled();
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EventMediatorInterface   $yem
     *
     * @return EveApiReadWriteInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function processEveApiXmlError(
        EveApiReadWriteInterface $data,
        EventMediatorInterface $yem
    ) {
        $simple = new SimpleXMLElement($data->getEveApiXml());
        if (empty($simple->error[0]['code'])) {
            return $data;
        }
        $code = (int)$simple->error[0]['code'];
        $mess = sprintf('Eve Error (%3$s): Received from API %1$s/%2$s - %4$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $code,
            (string)$simple->error[0]
        );
        if ($data->hasEveApiArgument('keyID')) {
            $mess .= ' for keyID = ' . $data->getEveApiArgument('keyID');
        }
        if ($code < 200) {
            if (strpos($mess, 'retry after') !== false) {
                $data->setCacheInterval(strtotime(substr($mess, -19) . '+00:00') - time());
            }
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return $data;
        }
        if ($code < 300) {
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $mess);
            return $data->setCacheInterval(86400);
        }
        if ($code > 903 && $code < 905) {
            // Major application or Yapeal error.
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::ALERT, $mess);
            return $data->setCacheInterval(86400);
        }
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
        return $data->setCacheInterval(300);
    }
}
