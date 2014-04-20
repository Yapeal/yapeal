<?php
/**
 * Contains abstract Section class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Database;

use AccessMask;
use Sections;
use Yapeal\Filesystem\FilterFileFinder;

/**
 * Abstract class used to hold common methods needed by Section* classes.
 *

 */
abstract class AbstractSection
{
    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $section = new \Sections(strtolower($this->section), false);
        } catch (\Exception $e) {
            \Logger::getLogger('yapeal')
                   ->error($e);
            // Section does not exist in utilSections table or other error occurred.
            $this->abort = true;
            return;
        }
        $this->mask = $section->activeAPIMask;
        $this->am = new \AccessMask();
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'class'
            . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . $this->section
            . DIRECTORY_SEPARATOR;
        $foundAPIs = FilterFileFinder::getStrippedFiles($path, $this->section);
        $knownAPIs = $this->am->apisToMask($foundAPIs, $this->section);
        if ($knownAPIs !== false) {
            $this->mask &= $knownAPIs;
        } else {
            $this->abort = true;
            $mess = 'No known APIs found for section ' . $this->section;
            \Logger::getLogger('yapeal')
                   ->error($mess);
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
     * @var bool Use to signal to child that parent constructor aborted.
     */
    protected $abort = false;
    /**
     * @var \AccessMask Hold AccessMask class used to convert between mask and APIs.
     */
    protected $am;
    /**
     * @var array Holds the mask of APIs for this section.
     */
    protected $mask;
    /**
     * @var string Hold section name.
     */
    protected $section;
}

