<?php
/**
 * Contains AccountStatus class.
 *
 * PHP version 5.5
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Account;

use LogicException;
use PDO;
use PDOException;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class AccountStatus
 */
class AccountStatus extends AccountSection
{
    use PreserverTrait;
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
    public function preserveEveApi(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $this->setYem($yem);
        $data = $event->getData();
        $xml = $data->getEveApiXml();
        $ownerID = $data->getEveApiArgument('keyID');
        $this->getYem()
             ->triggerLogEvent(
                 'Yapeal.Log.log',
                 Logger::DEBUG,
                 $this->getReceivedEventMessage($data, $eventName, __CLASS__)
             );
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserveToAccountStatus($xml, $ownerID)
                 ->preserveToMultiCharacterTraining($xml, $ownerID)
                 ->preserveToOffers($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                strtolower($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $ownerID
            );
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess, ['exception' => $exc]);
            $this->getPdo()
                 ->rollBack();
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @return array
     * @throws LogicException
     */
    protected function getActive()
    {
        $sql =
            $this->getCsq()
                 ->getActiveRegisteredAccountStatus();
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt =
                $this->getPdo()
                     ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            $mess = 'Database error message was ' . $exc->getMessage();
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        }
        return [];
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToAccountStatus(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'keyID' => $ownerID,
            'createDate' => null,
            'logonCount' => null,
            'logonMinutes' => null,
            'paidUntil' => null
        ];
        $this->valuesPreserveData($xml, $columnDefaults, 'accountAccountStatus');
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToMultiCharacterTraining($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID' => $ownerID,
            'trainingEnd' => null
        ];
        $tableName = 'accountMultiCharacterTraining';
        $sql =
            $this->getCsq()
                 ->getDeleteFromTableWithKeyID($tableName, $ownerID);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData($xml, $columnDefaults, $tableName, '//multiCharacterTraining/row');
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToOffers($xml, $ownerID)
    {
        $columnDefaults = [
            'keyID' => $ownerID,
            'offerID' => null,
            'offeredDate' => null,
            'from' => null,
            'to' => null,
            'ISK' => null
        ];
        $tableName = 'accountOffers';
        $sql =
            $this->getCsq()
                 ->getDeleteFromTableWithKeyID($tableName, $ownerID);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData($xml, $columnDefaults, $tableName, '//Offers/row');
        return $this;
    }
}
