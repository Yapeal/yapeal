<?php
/**
 * Contains EveApiXmlNetworkRetriever class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of yapeal
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
namespace Yapeal\Network;

use Guzzle\Http\Message;
use Guzzle\Http\Message\Response;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class EveApiXmlNetworkRetriever
 *
 * @author Stephen Gulick <stephenmg12@gmail.com>
 */
class EveApiXmlNetworkRetriever implements EveApiRetrieverInterface
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://api.eveonline.com/';
    /**
     * @var EveApiXmlDataInterface
     */
    protected $EveApiXmlData;
    /**
     * @var string
     */
    protected $urlTemplate;
    /**
     * @var
     */
    protected $networkRetriever;

    public function __construct(NetworkRetrieverInterface $networkRetriever)
    {
        //TODO: Implement construct()
        $this->networkRetriever = $networkRetriever;
    }
    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setNetworkRetriever($value)
    {
        $this->networkRetriever = $value;
        return $this;
    }
    /**
     * @param EveApiXmlDataInterface $data
     *
     * @return EveApiXmlDataInterface
     */
    public function retrieveEveApi(EveApiXmlDataInterface $data)
    {
        $this->EveApiXmlData = $data;
        /** @var $urlTemplateOptions array */
        $urlTemplateOptions=array(
            'baseUrl' => $this->baseUrl,
            'EveApiSectionName' => $this->EveApiXmlData->getEveApiSectionName(),
            'EveApiName' => $this->EveApiXmlData->getEveApiName()
        );
        /** @var $response response */
        $response = $this->networkRetriever->sendPost($this->urlTemplate,$urlTemplateOptions, $this->EveApiXmlData->getEveApiArguments());
        if ($response->getStatusCode() == '200') {
            $this->EveApiXmlData->setEveApiXml($response->getBody(true));
            return $this->EveApiXmlData;
        }
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setBaseUrl($value)
    {
        $this->baseUrl = $value;
        return $this;
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    /**
     * @param string $urlTemplate
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setUrlTemplate(
        $urlTemplate = 'https://{baseUrl}/{EveApiSectionName}/{EveApiName}.xml.aspx'
    ) {
        if (is_string($urlTemplate)) {
            $this->urlTemplate = $urlTemplate;
            return $this;
        } else {
            throw new \InvalidArgumentException;
        }
    }
}
