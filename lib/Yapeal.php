<?php
/**
 * Contains Yapeal class.
 *
 * PHP version 5.5
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
namespace Yapeal;

use FilePathNormalizer\FilePathNormalizerTrait;
use PDO;
use PDOException;
use PDOStatement;
use Yapeal\Configuration\Wiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Log\Logger;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Yapeal
 */
class Yapeal implements WiringInterface
{
    use EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param ContainerInterface $dic
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
        $this->wire($this->getDic());
    }
    /**
     * Starts Eve API processing
     *
     * @return int Returns 0 if everything was fine else something >= 1 for any
     * errors.
     * @throws \LogicException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function autoMagic()
    {
        $dic = $this->getDic();
        $this->setYem($dic['Yapeal.Event.EventMediator']);
        $mess = 'Let the magic begin!';
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        /**
         * @type CommonSqlQueries $csq
         */
        $csq = $dic['Yapeal.Database.CommonQueries'];
        $sql = $csq->getActiveApis();
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $sql);
        try {
            /**
             * @type PDO $pdo
             */
            $pdo = $dic['Yapeal.Database.Connection'];
            /**
             * @type PDOStatement $smt
             */
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could not access utilEveApi table';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess, ['exception' => $exc]);
            return 1;
        }
        // Always check APIKeyInfo.
        array_unshift(
            $result,
            [
                'apiName' => 'APIKeyInfo',
                'interval' => '300',
                'sectionName' => 'Account'
            ]
        );
        foreach ($result as $record) {
            /**
             * Get new Data instance from factory.
             *
             * @type EveApiReadWriteInterface $data
             */
            /** @noinspection DisconnectedForeachInstructionInspection */
            $data = $dic['Yapeal.Xml.Data'];
            $data->setEveApiName($record['apiName'])
                 ->setEveApiSectionName($record['sectionName'])
                 ->setCacheInterval($record['interval']);
            $this->emitEvents($data, 'start');
        }
        return 0;
    }
    /**
     * @param ContainerInterface $value
     *
     * @return self Fluent interface.
     */
    public function setDic(ContainerInterface $value)
    {
        $this->dic = $value;
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        (new Wiring($dic))->wireAll();
    }
    /**
     * @return array
     */
    protected function getActiveEveApiList()
    {
        $list = [];
        return $list;
    }
    /**
     * @return ContainerInterface
     */
    protected function getDic()
    {
        return $this->dic;
    }
    /**
     * @type ContainerInterface $dic
     */
    protected $dic;
}
