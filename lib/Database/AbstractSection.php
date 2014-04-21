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

use Yapeal\Database\Util\AccessMask;

/**
 * Abstract class used to hold common methods needed by Section* classes.
 *

 */
abstract class AbstractSection
{
    /**
     * Constructor
     *
     * @param \Yapeal\Database\Util\AccessMask|null $am
     * @param int                                   $activeAPIMask
     */
    public function __construct(
        AccessMask $am = null,
        $activeAPIMask
    ) {
        if ($am === null) {
            $am = new Util\AccessMask();
        }
        $this->am = $am;
        $this->mask = (int)$activeAPIMask;
    }
    /**
     * Function called by Yapeal.php to start section pulling XML from servers.
     *
     * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
     */
    abstract public function pullXML();
    /**
     * @var \Yapeal\Database\Util\AccessMask Hold Yapeal\Database\Util\AccessMask class used to convert between mask and APIs.
     */
    protected $am;
    /**
     * @var int Holds the mask of APIs for this section.
     */
    protected $mask;
    /**
     * @var string Hold section name.
     */
    protected $section;
}

