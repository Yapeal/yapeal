<?php
/**
 * Contains DatabaseInitializer class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use InvalidArgumentException;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Container\ContainerInterface;

/**
 * Class DatabaseInitializer
 */
class DatabaseInitializer extends AbstractDatabaseCommon
{
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves SQL from files and initializes database'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->addOptions();
        $help
            = <<<'HELP'
The <info>%command.full_name%</info> command is used to initialize (create) a new
 database and tables to be used by Yapeal. If you already have a
 config/yapeal.yaml file setup you can use the following:

    <info>php %command.full_name%</info>

EXAMPLES:
To use a configuration file in a different location:
    <info>%command.name% -c /my/very/special/config.yaml</info>

<info>NOTE:</info>
Only the Database section of the configuration file will be used.

You can also use the command before setting up a configuration file like so:
    <info>%command.name% -o "localhost" -d "yapeal" -u "YapealUser" -p "secret"

HELP;
        $this->setHelp($help);
    }
    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int     null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processCliOptions($input->getOptions());
        $this->wire($this->getDic());
        //var_dump($config);
        //exit(0);
        $this->processSql($output);
        return;
    }
    /**
     * @param OutputInterface $output
     *
     * @return string[]
     */
    protected function getCreateFileList(OutputInterface $output)
    {
        $fileNames = [
            'Database',
            'AccountTables',
            'ApiTables',
            'CharTables',
            'CorpTables',
            'EveTables',
            'MapTables',
            'ServerTables',
            'UtilTables',
            'CustomTables'
        ];
        $fileList = [];
        $path = $this->getDic()['Yapeal.baseDir'] . 'bin/sql/';
        if (!is_readable($path)) {
            $mess = sprintf(
                '<info>Could NOT access sql directory %1$s</info>',
                $path
            );
            $output->writeln($mess);
            return [];
        }
        foreach ($fileNames as $fileName) {
            $file = $path . 'Create' . $fileName . '.sql';
            if (!is_file($file)) {
                if ($fileName == 'CustomTables') {
                    continue;
                }
                $mess = sprintf(
                    '<info>Could NOT find SQL file %1$s</info>',
                    $file
                );
                $output->writeln($mess);
                continue;
            }
            $fileList[] = $file;
        }
        return $fileList;
    }
    /**
     * @param OutputInterface $output
     */
    protected function processSql(OutputInterface $output)
    {
        foreach ($this->getCreateFileList($output) as $fileName) {
            $sqlStatements = file_get_contents($fileName);
            if (false === $sqlStatements) {
                $mess = sprintf(
                    '<warning>Could NOT get contents of SQL file %1$s</warning>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            $output->writeln($fileName);
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
            $output->writeln('');
        }
    }
}
