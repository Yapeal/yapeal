<?php
/**
 * Contains ExceptionRenderer class.
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
/**
 * Class called by Logger when it needs to render an exception object. */
class ExceptionRenderer implements LoggerRendererObject
{
    /**
     * Method called by logger to render any exceptions in log messages.
     *
     * @param Exception $e The exception to be rendered.
     *
     * @return string Returns message string containing the exception message,
     * exception code (if known), and trace for all types of exceptions.
     */
    public function render($e)
    {
        $mess = $e->getMessage() . PHP_EOL;
        if ($e->getCode()) {
            $mess .= '     Code: ' . $e->getCode() . PHP_EOL;
        }
        $mess .= '    Trace:' . PHP_EOL;
        $mess .= $e->getTraceAsString() . PHP_EOL;
        $mess .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH) . PHP_EOL;
        if (method_exists($e, 'getPrevious') && $e->getPrevious() !== null) {
            $mess .= 'Caused by: ' . get_class($e->getPrevious());
        }
        return $mess;
    }
}
