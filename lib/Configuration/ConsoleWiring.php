<?php
/**
 * Contains ConsoleWiring class.
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
namespace Yapeal\Configuration;

use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;

/**
 * Class ConsoleWiring
 */
class ConsoleWiring extends Wiring
{
    /**
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function wireAll()
    {
        $this->wireConfig()
             ->wireError()
             ->wireEvent()
             ->wireLog()
             ->wireXml()
             ->wireXsl()
             ->wireXsd()
             ->wireCache()
             ->wireNetwork()
             ->wireDatabase();
        return $this;
    }
    /**
     * @return self Fluent interface.
     */
    protected function wireCache()
    {
        $dic = $this->dic;
        if ('none' !== $dic['Yapeal.Cache.fileSystemMode']) {
            if (empty($dic['Yapeal.FileSystem.CachePreserver'])) {
                $dic['Yapeal.FileSystem.CachePreserver'] = function () use ($dic) {
                    return new $dic['Yapeal.Cache.Handlers.preserver']($dic['Yapeal.Cache.cacheDir']);
                };
            }
            /**
             * @type \Yapeal\Event\EventMediatorInterface $mediator
             */
            $mediator = $dic['Yapeal.Event.EventMediator'];
            $mediator->addServiceSubscriberByEventList(
                'Yapeal.FileSystem.CachePreserver',
                ['Yapeal.EveApi.preserve' => ['preserveEveApi', 'last']]
            );
        }
        return $this;
    }
}
