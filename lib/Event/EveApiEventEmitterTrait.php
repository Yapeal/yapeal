<?php
/**
 * Contains EveApiEventEmitterTrait Trait.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\ContainerMediatorInterface;
use LogicException;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait EveApiEventEmitterTrait
 */
trait EveApiEventEmitterTrait
{
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventSuffix
     *
     * @return bool
     * @throws LogicException
     */
    protected function emitEvents(EveApiReadWriteInterface $data, $eventSuffix)
    {
        // Yapeal.EveApi.Section.Api.Suffix, Yapeal.EveApi.Api.Suffix,
        // Yapeal.EveApi.Section.Suffix, Yapeal.EveApi.Suffix
        $eventNames = explode(
            ',',
            sprintf(
                '%3$s.%1$s.%2$s.%4$s,%3$s.%2$s.%4$s,%3$s.%1$s.%4$s,%3$s.%4$s',
                ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'Yapeal.EveApi',
                $eventSuffix
            )
        );
        $event = null;
        foreach ($eventNames as $eventName) {
            $mess = 'Emitting event ' . $eventName;
            if (!$this->getYem()
                      ->hasListeners($eventName)
            ) {
                continue;
            }
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            $event = $this->getYem()
                          ->triggerEveApiEvent($eventName, $data);
            $data = $event->getData();
            if ($event->hasBeenHandled()) {
                $mess = 'Handled event ' . $eventName;
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
                break;
            }
        }
        if (null === $event || !$event->hasBeenHandled()) {
            $mess
                = sprintf(
                    'Nothing reported handling %4$s event of Eve API %1$s/%2$s for ownerID = %3$s',
                    lcfirst($data->getEveApiSectionName()),
                    $data->getEveApiName(),
                    $data->getEveApiArgument('keyID'),
                    $eventSuffix
                );
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param EventMediatorInterface $value
     *
     * @return self Fluent interface.
     */
    public function setYem(EventMediatorInterface $value)
    {
        $this->yem = $value;
        return $this;
    }
    /**
     * @return EventMediatorInterface
     * @throws LogicException
     */
    protected function getYem()
    {
        if (!$this->yem instanceof ContainerMediatorInterface) {
            $mess = 'Tried to use yem before it was set';
            throw new LogicException($mess);
        }
        return $this->yem;
    }
    /**
     * @type EventMediatorInterface $yem
     */
    protected $yem;
}
