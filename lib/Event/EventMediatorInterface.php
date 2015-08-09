<?php
/**
 * EventMediatorInterface.php
 *
 * PHP version 5.5
 *
 * @since  20150304 14:03
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\ContainerMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EventMediator
 */
interface EventMediatorInterface extends ContainerMediatorInterface
{
    /**
     * @param string                   $eventName
     * @param EveApiReadWriteInterface $data
     * @param EveApiEventInterface     $event
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerEveApiEvent(
        $eventName,
        EveApiReadWriteInterface $data,
        EveApiEventInterface $event = null
    );
    /**
     * @param string            $eventName
     * @param mixed             $level
     * @param string            $message
     * @param array             $context
     * @param LogEventInterface $event
     *
     * @return LogEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerLogEvent(
        $eventName,
        $level = Logger::DEBUG,
        $message = '',
        array $context = [],
        LogEventInterface $event = null
    );
}
