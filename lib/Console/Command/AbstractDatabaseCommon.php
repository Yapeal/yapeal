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
use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\WiringInterface;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealNormalizerException;
use Yapeal\Filesystem\FilePathNormalizer;

/**
 * Class AbstractDatabaseCommon
 */
abstract class AbstractDatabaseCommon extends Command implements WiringInterface
{
    /**
     * @param CommonSqlQueries $value
     *
     * @return self
     */
    public function setCsq($value)
    {
        $this->csq = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setCwd($value)
    {
        if (!is_string($value)) {
            $mess = 'Cwd MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->cwd = $value;
        return $this;
    }
    /**
     * @param ContainerInterface $value
     *
     * @return self
     */
    public function setDic(ContainerInterface $value)
    {
        $this->dic = $value;
        return $this;
    }
    /**
     * @param PDO $value
     *
     * @return self
     */
    public function setPdo(PDO $value)
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @throws YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.cwd'])) {
            $dic['Yapeal.cwd'] = $this->getNormalizedPath($this->getCwd());
        }
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] =
                $this->getNormalizedPath(dirname(dirname(dirname(__DIR__))));
        }
        $wiring = new ConsoleWiring($dic);
        $wiring->wireDefaults()
               ->wireConfiguration();
        $dic['Yapeal.Config.Parser'];
        $wiring->wireErrorLogger();
        $dic['Yapeal.Error.Logger'];
        $wiring->wireLogLogger()
               ->wireDatabase()
               ->wireCommonSqlQueries();
    }
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
     * @param string          $sqlStatements
     * @param string          $fileName
     * @param OutputInterface $output
     */
    protected function executeSqlStatements(
        $sqlStatements,
        $fileName,
        OutputInterface $output
    ) {
        $templates = [';', '{database}', '{table_prefix}', '$$'];
        $replacements =
            [
                '',
                $this->getDic()['Yapeal.Database.database'],
                $this->getDic()['Yapeal.Database.tablePrefix'],
                ';'
            ];
        $pdo = $this->getPdo($output);
        // Split up SQL into statements.
        $sqlStatements = explode(';', $sqlStatements);
        // Replace {database}, {table_prefix}, ';', and '$$' in statements.
        $sqlStatements =
            str_replace($templates, $replacements, $sqlStatements);
        foreach ($sqlStatements as $statement => $sql) {
            $sql = trim($sql);
            // 5 is a 'magic' number that I think is shorter than any legal SQL
            // statement.
            if (strlen($sql) < 5) {
                continue;
            }
            try {
                //$output->writeln($sql);
                $pdo->exec($sql);
            } catch (PDOException $exc) {
                $mess = sprintf(
                    '<error>Sql failed in %1$s on statement %2$s with (%3$s) %4$s</error>',
                    $fileName,
                    $statement,
                    $exc->getCode(),
                    $exc->getMessage()
                );
                $output->writeln([$sql, $mess]);
                exit(2);
            }
            $output->write('.');
        }
    }
    /**
     * @param OutputInterface $output
     *
     * @return CommonSqlQueries
     */
    protected function getCsq(OutputInterface $output)
    {
        if (is_null($this->csq)) {
            $this->csq = $this->getDic()['Yapeal.Database.CommonQueries'];
        }
        if (empty($this->csq)) {
            $output->writeln('<error>Tried to use csq before it was set</error>');
            exit(2);
        }
        return $this->csq;
    }
    /**
     * @return string
     */
    protected function getCwd()
    {
        return $this->cwd;
    }
    /**
     * @return ContainerInterface
     */
    protected function getDic()
    {
        return $this->dic;
    }
    /**
     * @param string $path
     *
     * @throws YapealNormalizerException
     * @return string
     */
    protected function getNormalizedPath($path)
    {
        $fpn = new FilePathNormalizer();
        return $fpn->normalizePath($path);
    }
    /**
     * @param OutputInterface $output
     *
     * @return PDO
     */
    protected function getPdo(OutputInterface $output)
    {
        if (is_null($this->pdo)) {
            try {
                $this->pdo = $this->getDic()['Yapeal.Database.Connection'];
            } catch (PDOException $exc) {
                $mess = sprintf(
                    '<error>Could NOT connect to database. Database error was (%1$s) %2$s</error>',
                    $exc->getCode(),
                    $exc->getMessage()
                );
                $output->writeln($mess);
                exit(2);
            }
        }
        return $this->pdo;
    }
    /**
     * @param array $options
     *
     * @return self
     */
    protected function processCliOptions(array $options)
    {
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
            $this->getDic()['Yapeal.Config.configDir'] =
                dirname($options['configFile']);
            $this->getDic()['Yapeal.Config.fileName'] =
                basename($options['configFile']);
        }
        return $this;
    }
    /**
     * @type CommonSqlQueries $csq
     */
    protected $csq;
    /**
     * @type string $cwd
     */
    protected $cwd;
    /**
     * @type ContainerInterface $dic
     */
    protected $dic;
    /**
     * @type PDO $pdo
     */
    protected $pdo;
}
