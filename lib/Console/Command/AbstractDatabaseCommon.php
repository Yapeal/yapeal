<?php
/**
 * Contains AbstractDatabaseCommon class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Console\CommandToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealConsoleException;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;

/**
 * Class AbstractDatabaseCommon
 */
abstract class AbstractDatabaseCommon extends Command implements WiringInterface
{
    use CommandToolsTrait, FilePathNormalizerTrait;
    /**
     * @param ContainerInterface $dic
     *
     * @throws YapealDatabaseException
     * @throws YapealException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.cwd'])) {
            $dic['Yapeal.cwd'] = $this->getFpn()
                                      ->normalizePath($this->getCwd());
        }
        $path = $this->getFpn()
                     ->normalizePath(dirname(dirname(dirname(__DIR__))));
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] = $path;
        }
        if (empty($dic['Yapeal.vendorParentDir'])) {
            $vendorPos = strpos($path, 'vendor/');
            if (false !== $vendorPos) {
                $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            }
        }
        (new ConsoleWiring($dic))->wireConfig()
                                 ->wireError()
                                 ->wireLog()
                                 ->wireDatabase();
    }
    /**
     * @param OutputInterface $output
     */
    abstract protected function processSql(OutputInterface $output);
    /**
     *
     */
    protected function addOptions()
    {
        $this->addOption(
            'configFile',
            'c',
            InputOption::VALUE_REQUIRED,
            'Configuration file to get settings from.'
        )
             ->addOption(
                 'database',
                 'd',
                 InputOption::VALUE_REQUIRED,
                 'Name of the database.'
             )
             ->addOption(
                 'hostName',
                 'o',
                 InputOption::VALUE_REQUIRED,
                 'Host name for database server.'
             )
             ->addOption(
                 'password',
                 'p',
                 InputOption::VALUE_REQUIRED,
                 'Password used to access database.'
             )
             ->addOption(
                 'platform',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Platform of database driver. Currently only "mysql".'
             )
             ->addOption(
                 'port',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Port number for remote server. Only needed if using http connection.'
             )
             ->addOption(
                 'tablePrefix',
                 't',
                 InputOption::VALUE_REQUIRED,
                 'Prefix for database table names.'
             )
             ->addOption(
                 'userName',
                 'u',
                 InputOption::VALUE_REQUIRED,
                 'User name used to access database.'
             );
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
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @throws YapealConsoleException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processCliOptions($input->getOptions());
        $this->wire($this->getDic());
        $this->processSql($output);
        return;
    }
    /**
     * @param string          $sqlStatements
     * @param string          $fileName
     * @param OutputInterface $output
     *
     * @throws YapealConsoleException
     * @throws YapealDatabaseException
     */
    protected function executeSqlStatements(
        $sqlStatements,
        $fileName,
        OutputInterface $output
    ) {
        $templates = [
            ';',
            '{database}',
            '{engine}',
            '{ engine}',
            '{table_prefix}',
            '$$'
        ];
        $replacements = [
            '',
            $this->getDic()['Yapeal.Database.database'],
            $this->getDic()['Yapeal.Database.engine'],
            $this->getDic()['Yapeal.Database.engine'],
            $this->getDic()['Yapeal.Database.tablePrefix'],
            ';'
        ];
        $pdo = $this->getPdo();
        // Split up SQL into statements on ';'.
        // Replace {database}, {table_prefix}, {engine}, ';', and '$$' in statements.
        /**
         * @type string[] $statements
         */
        $statements = str_replace(
            $templates,
            $replacements,
            explode(';', $sqlStatements)
        );
        foreach ($statements as $statement => $sql) {
            $sql = trim($sql);
            // 5 is a 'magic' number that I think is shorter than any legal SQL
            // statement.
            if (5 > strlen($sql)) {
                continue;
            }
            try {
                $pdo->exec($sql);
            } catch (PDOException $exc) {
                $mess = $sql . PHP_EOL;
                $mess .= sprintf(
                    'Sql failed in %1$s on statement %2$s with (%3$s) %4$s',
                    $fileName,
                    $statement,
                    $exc->getCode(),
                    $exc->getMessage()
                );
                throw new YapealDatabaseException($mess, 2);
            }
            $output->write('.');
        }
    }
    /**
     * @param array $options
     *
     * @return AbstractDatabaseCommon
     * @throws YapealConsoleException
     *
     */
    protected function processCliOptions(
        array $options
    ) {
        $base = 'Yapeal.Database.';
        foreach ([
                     'class',
                     'database',
                     'hostName',
                     'password',
                     'platform',
                     'tablePrefix',
                     'userName'
                 ] as $option) {
            if (!empty($options[$option])) {
                $this->getDic()[$base . $option] = $options[$option];
            }
        }
        if (!empty($options['configFile'])) {
            $this->getDic()['Yapeal.Config.configDir']
                = dirname($options['configFile']);
            $this->getDic()['Yapeal.Config.fileName']
                = basename($options['configFile']);
        }
        return $this;
    }
}
