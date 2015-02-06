<?php
/**
 * LogEventInterface.php
 *
 * PHP version 5.4
 *
 * @since  20150104 14:09
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\EventInterface;

/**
 * Class LogEvent
 */
interface LogEventInterface extends EventInterface
{
    /**
     * @return array
     */
    public function getContext();
    /**
     * @return mixed
     */
    public function getLevel();
    /**
     * @return string
     */
    public function getMessage();
    /**
     * @param array $value
     *
     * @return self
     */
    public function setContext(array $value);
    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setLevel($value);
    /**
     * @param string $value
     *
     * @return self
     */
    public function setMessage($value);
}
