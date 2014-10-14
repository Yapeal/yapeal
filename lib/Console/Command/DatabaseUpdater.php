<?php
/**
 * Contains DatabaseUpdater class.
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

use DirectoryIterator;
use PDO;
use PDOException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Database\CommonSqlQueries;

/**
 * Class DatabaseUpdater
 */
class DatabaseUpdater extends AbstractDatabaseCommon
{
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
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
     * @param OutputInterface $output
     */
    protected function addDatabaseProcedure(
        OutputInterface $output
    ) {
        $name = 'DatabaseUpdater::addDatabaseProcedure';
        $output->writeln($name);
        $csq = $this->getCsq($output);
        $this->executeSqlStatements(
            $csq->getDropAddOrModifyColumnProcedure()
            . PHP_EOL . $csq->getCreateAddOrModifyColumnProcedure(),
            $name,
            $output
        );
        $output->writeln('');
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
     * @param OutputInterface $output
     */
    protected function dropDatabaseProcedure(
        OutputInterface $output
    ) {
        $name = 'DatabaseUpdater::dropDatabaseProcedure';
        $output->writeln($name);
        $this->executeSqlStatements(
            $this->getCsq($output)
                 ->getDropAddOrModifyColumnProcedure(),
            $name,
            $output
        );
        $output->writeln('');
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
        $this->processSql($output);
        return;
    }
    /**
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getLatestDatabaseVersion(OutputInterface $output)
    {
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $this->getDic()['Yapeal.Database.CommonQueries'];
        $sql = $csq->getUtilLatestDatabaseVersion();
        try {
            $result = $this->getPdo($output)
                           ->query($sql, PDO::FETCH_NUM);
            $version = $result->fetchColumn();
            //$output->writeln('database column version = ' . $version);
            $result->closeCursor();
        } catch (PDOException $exc) {
            $mess
                = '<warning>Could NOT get latest database version using default 197001010001</warning>';
            $output->writeln([$sql, $mess]);
            $version = '197001010001';
        }
        return sprintf('%1$012s', $version);
    }
    /**
     * @param OutputInterface $output
     *
     * @return string[]
     */
    protected function getUpdateFileList(OutputInterface $output)
    {
        $fileNames = [];
        $path = $this->getDic()['Yapeal.baseDir'] . 'bin/sql/updates/';
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
            if ($fileInfo->getExtension() != 'sql') {
                continue;
            }
            //$output->writeln(str_replace('\\', '/', $fileInfo->getPathname()));
            $fileNames[] = str_replace('\\', '/', $fileInfo->getPathname());
        }
        return $fileNames;
    }
    /**
     * @param OutputInterface $output
     */
    protected function processSql(
        OutputInterface $output
    ) {
        $this->addDatabaseProcedure($output);
        foreach ($this->getUpdateFileList($output) as $fileName) {
            $latestVersion = $this->getLatestDatabaseVersion($output);
            //$output->writeln('$latestVersion = ' . $latestVersion);
            if (!is_file($fileName)) {
                $mess = sprintf(
                    '<info>Could NOT find SQL file %1$s</info>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            $updateVersion = basename($fileName, '.sql');
            //$output->writeln('$updateVersion = ' . $updateVersion);
            if ($updateVersion <= $latestVersion) {
                $mess = sprintf(
                    '<info>Skipping SQL file %1$s since its <= the latest database version %2$s</info>',
                    basename($fileName),
                    $latestVersion
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
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
            $this->updateDatabaseVersion($updateVersion, $output);
            $output->writeln('');
        }
        $this->dropDatabaseProcedure($output);
    }
    /**
     * @param string          $updateVersion
     * @param OutputInterface $output
     *
     * @return self
     */
    protected function updateDatabaseVersion(
        $updateVersion,
        OutputInterface $output
    ) {
        /**
         * @var CommonSqlQueries $csq
         */
        $sql = $this->getCsq($output)
                    ->getUtilLatestDatabaseVersionUpdate();
        //$mess =sprintf( 'Updating database version to %1$s',$currentVersion);
        //$output->writeln([$sql, $mess]);
        try {
            $pdo = $this->getPdo($output);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$updateVersion]);
            $pdo->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                '<error>Database "version" update failed for %1$s</error>',
                $updateVersion
            );
            $output->writeln([$sql, $mess]);
            $this->getPdo($output)
                 ->rollBack();
            exit(2);
        }
        return $this;
    }
}
