<?php
/**
 * Contains GroupRetriever class.
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
namespace Yapeal\Xml;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GroupRetriever
 */
class GroupRetriever implements EveApiRetrieverInterface, LoggerAwareInterface
{
    /**
     * @param LoggerInterface            $logger
     * @param EveApiRetrieverInterface[] $retrieverList
     */
    public function __construct(
        LoggerInterface $logger,
        array $retrieverList = array()
    ) {
        $this->setRetrieverList($retrieverList);
        $this->setLogger($logger);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self
     */
    public function retrieveEveApi(EveApiReadWriteInterface &$data)
    {
        $retrievers = $this->getRetrieverList();
        if (empty($retrievers)) {
            $mess = 'No retrievers received';
            $this->getLogger()
                 ->warning($mess);
            return $this;
        }
        foreach ($this->getRetrieverList() as $retriever) {
            $retriever->retrieveEveApi($data);
            if ($data->getEveApiXml() !== false) {
                return $this;
            }
        }
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
     * @param EveApiRetrieverInterface[] $value
     *
     * @return self
     */
    public function setRetrieverList(array $value)
    {
        $this->retrieverList = $value;
        return $this;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    /**
     * @return EveApiRetrieverInterface[]
     */
    protected function getRetrieverList()
    {
        return $this->retrieverList;
    }
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var EveApiRetrieverInterface[]
     */
    protected $retrieverList;
}
