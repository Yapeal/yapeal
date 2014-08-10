<?php
/**
 * Contains FilePathNormalizer class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Filesystem;

use Yapeal\Exception\YapealNormalizerException;

/**
 * Class FilePathNormalizer
 */
class FilePathNormalizer
{
    /**
     * @param string $path
     * @param bool   $absoluteRequired
     *
     * @throws YapealNormalizerException
     * @return string
     */
    public function normalizePath($path, $absoluteRequired = true)
    {
        $path = str_replace('\\', '/', $path);
        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:alpha:]][[:alnum:]]+://)*)';
        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = array();
        if (!preg_match($regExp, $path, $parts)) {
            $mess = 'Path is not valid was given ' . $path;
            throw new YapealNormalizerException($mess, 1);
        }
        $wrappers = $parts['wrappers'];
        // vfsStream does NOT allow absolute path.
        if ('vfs://' == substr($wrappers, -6)) {
            $absoluteRequired = false;
        }
        if ($absoluteRequired && empty($parts['root'])) {
            $mess =
                'Path NOT absolute missing drive or root was given ' . $path;
            throw new YapealNormalizerException($mess, 2);
        }
        $root = $parts['root'];
        $parts = $this->cleanPartsPath($parts['path']);
        $path = $wrappers . $root . implode('/', $parts);
        if ('/' != substr($path, -1)) {
            $path .= '/';
        }
        return $path;
    }
    /**
     * @param string $path
     *
     * @return string[]
     * @throws YapealNormalizerException
     */
    protected function cleanPartsPath($path)
    {
        // Drop all leading and trailing "/"s.
        $path = trim($path, '/');
        // Drop pointless consecutive "/"s.
        while (false !== strpos($path, '//')) {
            $path = str_replace('//', '/', $path);
        }
        $parts = array();
        foreach (explode('/', $path) as $part) {
            if ('.' == $part || '' == $part) {
                continue;
            }
            if ('..' == $part) {
                if (count($parts) < 1) {
                    $mess = 'Can NOT go above root path but given ' . $path;
                    throw new YapealNormalizerException($mess, 1);
                }
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }
        return $parts;
    }
}
