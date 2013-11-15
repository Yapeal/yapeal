<?php
/**
 * Contains abstract Section class.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2013, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Section;

use Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Yapeal\Autoload\FilterFileFinder;
use Yapeal\Util\AccessMask;
use Yapeal\Util\Sections;

/**
 * Abstract class used to hold common methods needed by Section* classes.
 *
 * @package Yapeal\Section
 */
abstract class ASection implements LoggerAwareInterface
{
    /**
     * @var bool Use to signal to child that parent constructor aborted.
     */
    protected $abort = false;
    /**
     * @var AccessMask Hold AccessMask class used to convert between mask and APIs.
     */
    protected $am;
    /**
     * @var LoggerInterface Holds logger instance used for all logging.
     */
    protected $logger;
    /**
     * @var array Holds the mask of APIs for this section.
     */
    protected $mask;
    /**
     * @var string Hold section name.
     */
    protected $section;
    /**
     * Constructor
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
        try {
            $section =
                new Sections(strtolower($this->section), false);
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());
            // Section does not exist in utilSections table or other error occurred.
            $this->abort = true;
            return;
        }
        if ($section->isActive == 0) {
            // Skip inactive sections.
            $this->abort = true;
            return;
        };
        $this->mask = $section->activeAPIMask;
        // Skip if there's no active APIs for this section.
        if ($this->mask == 0) {
            $this->abort = true;
            return;
        };
        $this->am = new AccessMask();
        $path = YAPEAL_CLASS . 'api' . DS . $this->section . DS;
        $foundAPIs = FilterFileFinder::getStrippedFiles($path, $this->section);
        $knownAPIs = $this->am->apisToMask($foundAPIs, $this->section);
        if ($knownAPIs !== false) {
            $this->mask &= $knownAPIs;
        } else {
            $this->abort = true;
            $mess = 'No known APIs found for section ' . $this->section;
            $this->logger->log(LogLevel::ERROR, $mess);
            return;
        }
    }
    /**
     * Function called by Yapeal.php to start section pulling XML from servers.
     *
     * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
     */
    abstract public function pullXML();
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface|null $logger
     *
     * @return null|void
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }
}

