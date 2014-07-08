<?php
/**
 * Contains WalletJournal class.
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
namespace Yapeal\Database\Char;

use LogicException;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class WalletJournal
 */
class WalletJournal extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     *
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers
    ) {
        $data->addEveApiArgument('accountKey', '1000');
        $data->addEveApiArgument('rowCount', '2560');
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $charID = $data->getEveApiArgument('characterID');
        /**
         * @var EveApiReadWriteInterface $data
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
        if (!$this->preserve($data->getEveApiXml(), $charID, '1000')) {
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @throws LogicException
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        $accountKey
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToWalletJournal($xml, $ownerID, $accountKey);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @return self
     */
    protected function preserverToWalletJournal(
        $xml,
        $ownerID,
        $accountKey
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'accountKey' => $accountKey,
            'date' => null,
            'refID' => null,
            'refTypeID' => null,
            'owner1TypeID' => null,
            'ownerID1' => null,
            'ownerName1' => null,
            'owner2TypeID' => null,
            'ownerID2' => null,
            'ownerName2' => null,
            'argID1' => null,
            'argName1' => null,
            'amount' => null,
            'balance' => null,
            'reason' => null,
            'taxReceiverID' => '0',
            'taxAmount' => '0'
        );
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'charWalletJournal'
        );
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 2097152;
}
