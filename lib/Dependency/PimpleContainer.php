<?php
/**
 * This file is a simple wrapper for Pimple so Yapeal is future proofed against
 * changes to it's API.
 *
 * PHP 5.3
 *
 * This is a wrapper / adapter to Pimple. Ran into many problems in composer etc
 * with trying to use version 2.0.x or master from Pimple as most other projects
 * on Packagest require 1.1.x and there was no way to resolved the conflicts.
 *
 * Original the way I (Michael Cummings) thought of to solve the conflict was to
 * just add copy of the Pimple file from version 2.0.0 into Yapeal itself but
 * did mean having to maintain it from then on and manually merging any local
 * changes with external ones from the main project which is NOT a good long
 * term way to do things.
 *
 * I decided I need a better solution and after a few weeks of looking at
 * submodules and the drawbacks with them I decided they would NOT work, but did
 * ran into ```git subtree``` which does seem to fit in to what was needed and
 * does NOT have the drawbacks that submodules had.
 *
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
namespace Yapeal\Dependency;

use Pimple\Container;

/**
 * PimpleContainer class.
 *
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @since  1.1.x-WIP
 */
class PimpleContainer extends Container implements DependenceInterface
{
}
