<?php
/**
 * Contains EveApiRetriever class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
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
namespace Yapeal\Console\Application;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Yapeal\Console\Command as YCC;

/**
 * Class EveApiRetriever
 */
class EveApiRetriever extends Application
{
    /**
     * Constructor.
     *
     * @param string $name    The name of the application
     * @param string $version The version of the application
     * @param string $cwd
     *
     * @api
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', $cwd)
    {
        $this->setCwd($cwd);
        parent::__construct($name, $version);
    }
    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();
        return $inputDefinition;
    }
    /**
     * @param string $cwd
     *
     * @return self
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;
        return $this;
    }
    /**
     * @type string
     */
    protected $cwd;
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return basename(str_replace('\\', '/', __CLASS__));
    }
    /**
     * @return mixed
     */
    protected function getCwd()
    {
        return $this->cwd;
    }
    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new YCC\EveApiRetriever(
            basename(str_replace('\\', '/', __CLASS__)),
            $this->getCwd()
        );
        return $defaultCommands;
    }
}
