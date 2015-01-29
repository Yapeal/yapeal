<?php
/**
 * EventAwareLoggerInterface.php
 *
 * PHP version 5.4
 *
 * @since  20150110 19:14
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Yapeal\Event\LogEventInterface;

/**
 * Class Logger
 */
interface EventAwareLoggerInterface
{
    /**
     * @param LogEventInterface $event
     */
    public function logEvent(LogEventInterface $event);
}
