<?php
/**
 * Contains AbstractCharSection class.
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Char;

use PDO;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\SectionNameTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class AbstractCharSection
 *
 * @property-read string $mask
 */
abstract class AbstractCharSection extends AbstractCommonEveApi
{
    use SectionNameTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->debug(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        $active = $this->getActiveCharacters();
        if (empty($active)) {
            $this->getLogger()
                ->info('No active characters found');
            return;
        }
        foreach ($active as $char) {
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $char['characterID']
            )
            ) {
                continue;
            }
            $data->setEveApiArguments($char)
                 ->setEveApiXml();
            if (!$this->oneShot($data, $retrievers, $preservers)) {
                continue;
            }
            $this->updateCachedUntil($data, $interval, $char['characterID']);
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     *
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers
    ) {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $char = $data->getEveApiArguments();
        $charID = $char['characterID'];
        /**
         * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
         */
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $charID
            );
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $charID
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $preservers->preserveEveApi($data);
        $this->preserve(
            $data->getEveApiXml(),
            $charID
        );
        return true;
    }
    /**
     * @var $mask
     */
    protected $mask;
    /**
     * @return array
     */
    protected function getActiveCharacters()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredCharacters($this->getMask());
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active characters';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return array();
        }
    }
    /**
     * @return int
     */
    protected function getMask()
    {
        return $this->mask;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    abstract protected function preserve(
        $xml,
        $ownerID
    );
}
