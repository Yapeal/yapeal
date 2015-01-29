<?php
/**
 * Contains CommandToolsTrait trait.
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
namespace Yapeal\Console;

use InvalidArgumentException;
use PDO;
use PDOException;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Sql\CommonSqlQueries;

/**
 * Trait CommandToolsTrait
 */
trait CommandToolsTrait
{
    /**
     * @param CommonSqlQueries $value
     *
     * @return self
     */
    public function setCsq(CommonSqlQueries $value)
    {
        $this->csq = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setCwd($value)
    {
        if (!is_string($value)) {
            $mess = 'Cwd MUST be string but given ' . gettype($value);
            throw new InvalidArgumentException($mess);
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
     * @param OutputInterface $output
     *
     * @return CommonSqlQueries
     */
    protected function getCsq(OutputInterface $output)
    {
        if (null === $this->csq) {
            $this->csq = $this->getDic(
                $output
            )['Yapeal.Database.CommonQueries'];
        }
        if (!$this->csq instanceof CommonSqlQueries) {
            $output->writeln(
                '<error>Tried to use csq before it was set</error>'
            );
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
     * @param OutputInterface $output
     *
     * @return ContainerInterface
     */
    protected function getDic(OutputInterface $output)
    {
        if (!$this->dic instanceof ContainerInterface) {
            $output->writeln(
                '<error>Tried to use dic before it was set</error>'
            );
            exit(2);
        }
        return $this->dic;
    }
    /**
     * @param OutputInterface $output
     *
     * @return PDO
     */
    protected function getPdo(OutputInterface $output)
    {
        if (null === $this->pdo) {
            try {
                $this->pdo = $this->getDic(
                    $output
                )['Yapeal.Database.Connection'];
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
