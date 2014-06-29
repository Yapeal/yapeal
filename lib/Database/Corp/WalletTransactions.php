<?php
/**
 * Contains WalletTransactions class.
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
namespace Yapeal\Database\Corp;

use PDOException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class WalletTransactions
 */
class WalletTransactions extends AbstractCorpSection
{
    /**
     * @var int $mask
     */
    protected $mask = 2097152;
    /**
     * @return string
     */
    protected function getApiName()
    {
        if (empty($this->apiName)) {
            $this->apiName = basename(str_replace('\\', '/', __CLASS__));
        }
        return $this->apiName;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        $accountKey = null
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToWalletTransactions(
                 $xml,
                     $ownerID,
                     $accountKey
            );
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @return self
     */
    protected function preserverToWalletTransactions(
        $xml,
        $ownerID,
        $accountKey
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'accountKey' => $accountKey,
            'clientID' => null,
            'clientName' => null,
            'clientTypeID' => null,
            'journalTransactionID' => null,
            'price' => null,
            'quantity' => null,
            'stationID' => null,
            'stationName' => null,
            'transactionDateTime' => null,
            'transactionFor' => null,
            'transactionID' => null,
            'transactionType' => null,
            'typeID' => null,
            'typeName' => null
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('charWalletTransactions')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
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
        $accountKeys = range(1000, 1006);
        foreach ($accountKeys as $key) {
            $data->addEveApiArgument('accountKey', $key);
            $data->addEveApiArgument('rowCount', '2540');
            parent::oneShot($data, $retrievers, $preservers);
        }
    }
}
