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
use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Container\ContainerInterface;
use Yapeal\Database\CommonSqlQueries;
use Yapeal\Exception\YapealDatabaseException;
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
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name = null, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves SQL from files and updates database'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
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
                str_replace('\\', '/', dirname(dirname(dirname(__DIR__))))
                . '/';
        }
        $wiring = new ConsoleWiring($dic);
        $wiring->wireDefaults()
               ->wireConfiguration();
        $dic['Yapeal.Config.Parser'];
        $wiring->wireErrorLogger();
        $dic['Yapeal.Error.Logger'];
        $wiring->wireLogLogger()
               ->wireDatabase()
               ->wireCommonSqlQueries()
               ->wireRetriever()
               ->wirePreserver();
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
     * @param InputInterface $input An InputInterface instance
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
        $this->processSql($output);
        return;
    }

    /**
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getCurrentDatabaseVersion(OutputInterface $output)
    {
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $this->getDic()['Yapeal.Database.CommonQueries'];
        $sql = $csq->getUtilCurrentDatabaseVersion();
        try {
            $result = $this->getPdo($output)
                           ->query($sql, PDO::FETCH_NUM);
            $version = $result->fetchColumn();
            //$output->writeln($version);
            $result->closeCursor();
        } catch (PDOException $exc) {
            $mess =
                '<warning>Could NOT get database version using default 197001010001</warning>';
            $output->writeln([$sql, $mess]);
            $version = '197001010001';
        }
        return sprintf('%1$012d', $version);
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
            //$output->writeln(str_replace('\\', '/', $fileInfo->getPathname()));
            $fileNames[] = str_replace('\\', '/', $fileInfo->getPathname());
        }
        return $fileNames;
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
     * @param OutputInterface $output
     */
    protected function processSql(
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
        $currentVersion = $this->getCurrentDatabaseVersion($output);
        foreach ($this->getUpdateFileList($output) as $fileName) {
            if (!is_file($fileName)) {
                $mess = sprintf(
                    '<info>Could NOT find SQL file %1$s</info>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            if (basename($fileName, '.sql') <= $currentVersion) {
                $mess = sprintf(
                    '<info>Skipping SQL file %1$s <= database current version %2$s</info>',
                    basename($fileName),
                    $currentVersion
                );
                $output->writeln($mess);
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
            foreach ($sqlStatements as $statement => $sql) {
                $sql = trim($sql);
                // 5 is a 'magic' number that I think is shorter than any sql statement.
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
            $currentVersion = basename($fileName, '.sql');
            $this->updateCurrentDatabaseVersion($currentVersion, $output);
            $output->writeln('');
        }
    }
    /**
     * @param string          $currentVersion
     * @param OutputInterface $output
     *
     * @return self
     */
    protected function updateCurrentDatabaseVersion(
        $currentVersion,
        OutputInterface $output
    )
    {
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $this->getDic()['Yapeal.Database.CommonQueries'];
        $sql = $csq->getUtilCurrentDatabaseVersionUpdate($currentVersion);
        //$mess =sprintf( 'Updating database version to %1$s',$currentVersion);
        //$output->writeln([$sql, $mess]);
        try {
            $this->getPdo($output)
                 ->beginTransaction();
            $stmt = $this->getPdo($output)
                         ->prepare($sql);
            $stmt->execute([$currentVersion]);
            $this->getPdo($output)
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                '<error>Database "version" update failed for %1$s</error>',
                $currentVersion
            );
            $output->writeln([$sql, $mess]);
            $this->getPdo($output)
                 ->rollBack();
            exit(2);
        }
        return $this;
    }
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
