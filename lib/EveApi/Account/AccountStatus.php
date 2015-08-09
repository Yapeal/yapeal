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
use Yapeal\EveApi\EveApiNameTrait;
use Yapeal\EveApi\EveSectionNameTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class AccountStatus
 */
class AccountStatus extends AccountSection
{
    use EveApiNameTrait, EveSectionNameTrait;
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
        if ($event->hasBeenHandled()) {
            $mess = 'Received already handled event ' . $eventName;
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return $event;
        }
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
//        $fileName = sprintf(
//            '%1$s/cache/%2$s/%3$s.xml',
//            dirname(dirname(dirname(__DIR__))),
//            $data->getEveApiSectionName(),
//            $data->getEveApiName()
//        );
//        file_put_contents($fileName, $data->getEveApiXml());
        return $event;
    }
    /**
     * @throws LogicException
     * @return array
     */
    protected function getActive()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredAccountStatus();
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            $mess = 'Database error message was ' . $exc->getMessage();
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return [];
        }
    }
}
