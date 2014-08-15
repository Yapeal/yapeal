<?php
/**
 * Contains DatabaseUpdater class.
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
namespace Yapeal\Console\Command;

use DirectoryIterator;
use mysqli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Yapeal\Exception\YapealNormalizerException;
use Yapeal\Filesystem\FilePathNormalizer;

/**
 * Class DatabaseUpdater
 */
class DatabaseUpdater extends Command
{
    /**
     * @param string|null $name
     * @param string      $cwd
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name = null, $cwd)
    {
        $this->setDescription(
            'Retrieves SQL from files and updates database'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        parent::__construct($name);
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
     * Configures the current command.
     */
    protected function configure()
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
        $help = <<<'HELP'
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
        $options = $input->getOptions();
        $configFile = $this->getCwd() . '/config/yapeal.yaml';
        if (!empty($options['configFile'])) {
            $configFile =
                $this->getNormalizedPath($options['configFile']);
        }
        $config = [];
        if ($this->isAccessibleConfigFile($configFile, $output)) {
            $mess = sprintf(
                'Using configuration file %1$s for settings',
                $configFile
            );
            $output->writeln($mess);
            $config =
                $this->getConfigFileSettings($configFile)['Yapeal']['Database'];
        }
        $config = array_replace(
            ['tablePrefix' => ''],
            $config
        );
        foreach ($options as $key => $value) {
            if ($value !== null) {
                $config[$key] = $value;
            }
        }
        $required = ['database', 'hostName', 'password', 'userName'];
        $mess = [];
        foreach ($required as $setting) {
            if (empty($config[$setting])) {
                $mess[] =
                    '<error>Missing required setting ' . $setting . '</error>';
            }
        }
        if (!empty($mess)) {
            $output->writeln($mess);
            exit(1);
        }
        //var_dump($config);
        //exit(0);
        $this->processSql($this->getDbCon($config, $output), $config, $output);
        return;
    }
    /**
     * @param string $configFile
     *
     * @throws ParseException
     * @return array
     */
    protected function getConfigFileSettings($configFile)
    {
        $yaml = new Yaml();
        return $yaml->parse(file_get_contents($configFile));
    }
    /**
     * @param mysqli          $mysqli
     * @param array           $options
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getCurrentDatabaseVersion(
        mysqli $mysqli,
        array $options,
        OutputInterface $output
    ) {
        $sql = sprintf(
            'SELECT "version" FROM "%1$s"."%2$sutilDatabaseVersion"',
            $options['database'],
            $options['tablePrefix']
        );
        $result = $mysqli->query($sql, MYSQLI_NUM);
        if ($result === false) {
            $mess =
                '<warning>Could NOT get database version using 0000</warning>';
            $output->writeln($mess);
            $mysqli->close();
            return ['0000'];
        }
        $version = $result->fetch_row();
        $result->close();
        return (string)$version[0];
    }
    /**
     * @return string
     */
    protected function getCwd()
    {
        return $this->cwd;
    }
    /**
     * @param array           $options
     * @param OutputInterface $output
     *
     * @return mysqli
     */
    protected function getDbCon(array $options, OutputInterface $output)
    {
        if (is_null($this->mysqli)) {
            $this->mysqli = @new mysqli(
                $options['hostName'],
                $options['userName'],
                $options['password'],
                ''
            );
            if (mysqli_connect_error()) {
                $mess = sprintf(
                    '<error>Could NOT connect to MySQL. MySQL error was (%1$s) %2$s</error>',
                    mysqli_connect_errno(),
                    mysqli_connect_error()
                );
                $output->writeln($mess);
                exit(2);
            }
        }
        return $this->mysqli;
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
     * @return string[]
     */
    protected function getUpdateFileList(OutputInterface $output)
    {
        $fileNames = [];
        $path = $this->getCwd() . '/bin/sql/updates/';
        if (!is_readable($path)) {
            $mess = sprintf(
                '<info>Could NOT access update directory %1$s</info>',
                $path
            );
            $output->writeln($mess);
            return $fileNames;
        }
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            };
            if ($fileInfo->getExtension() !== 'sql') {
                continue;
            }
            $fileNames[] = str_replace('\\', '/', $fileInfo->getPathname());
        }
        return $fileNames;
    }
    /**
     * @param string          $configFile
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function isAccessibleConfigFile(
        $configFile,
        OutputInterface $output
    ) {
        if (is_readable($configFile) && is_file($configFile)) {
            return true;
        }
        $mess = sprintf(
            '<warning>Was given %1$s which is not accessible</warning>',
            $configFile
        );
        $output->writeln($mess);
        return false;
    }
    /**
     * @param mysqli          $mysqli
     * @param array           $options
     * @param OutputInterface $output
     */
    protected function processSql(
        mysqli $mysqli,
        $options,
        OutputInterface $output
    ) {
        $templates = [';', '{database}', '{table_prefix}'];
        $replacements =
            ['', $options['database'], $options['tablePrefix']];
        $currentVersion =
            $this->getCurrentDatabaseVersion($mysqli, $options, $output);
        foreach ($this->getUpdateFileList($output) as $fileName) {
            if (!is_file($fileName)) {
                $mess = sprintf(
                    '<info>Could NOT find SQL file %1$s</info>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            if (basename($fileName) <= $currentVersion) {
                continue;
            }
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
            // Split up SQL into statements.
            $sqlStatements = explode(';', $sqlStatements);
            // Replace {database}, {table_prefix}, and ';' in statements.
            $sqlStatements =
                str_replace($templates, $replacements, $sqlStatements);
            foreach ($sqlStatements as $line => $sql) {
                $sql = trim($sql);
                // 5 is a 'magic' number that I think is shorter than any sql statement.
                if (strlen($sql) < 5) {
                    continue;
                }
                if ($mysqli->query($sql) === false) {
                    $mess[] = sprintf(
                        '<error>Sql statement failed in %1$s on line %2$s with (%3$s) %4$s</error>',
                        $fileName,
                        $line,
                        $mysqli->errno,
                        $mysqli->error
                    );
                    $mess[] = $sql;
                    $output->writeln($mess);
                    $mysqli->close();
                    exit(2);
                }
                $output->write('.');
            }
            $currentVersion = basename($fileName);
            $this->updateCurrentDatabaseVersion(
                $currentVersion,
                $mysqli,
                $options,
                $output
            );
            $output->writeln('');
        }
    }
    /**
     * @param string          $currentVersion
     * @param mysqli          $mysqli
     * @param array           $options
     * @param OutputInterface $output
     */
    protected function updateCurrentDatabaseVersion(
        $currentVersion,
        mysqli $mysqli,
        array $options,
        OutputInterface $output
    ) {
        $sql = <<<'SQL'
UPDATE "%1$s"."%2$sutilDatabaseVersion" SET "version"='%3$s'
SQL;
        $sql = sprintf($sql, $options['database'], $options['tablePrefix']);
        if ($mysqli->query($sql) !== true) {
            $mess = sprintf(
                '<error>Database "version" update failed for %1$s</error>',
                $currentVersion
            );
            $output->writeln($mess);
            exit(2);
        }
    }
    /**
     * @type string
     */
    protected $cwd;
    /**
     * @type mysqli $mysqli
     */
    protected $mysqli;
}
