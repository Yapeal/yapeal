<?php
/**
 * Contains EveApiXmlDataTest class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of yapeal
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
namespace Yapeal\Test\Xml;

use Yapeal\Xml\EveApiXmlData;

/**
 * Class EveApiXmlDataTest
 */
class EveApiXmlDataTest extends \PHPUnit_Framework_TestCase
{
    public function testEveApiArguments()
    {
        $data = new EveApiXmlData();
        $this->assertAttributeEmpty('eveApiArguments', $data);
        $arg = array('arg1' => 'test1', 'arg2' => 'test2');
        $data->setEveApiArguments($arg);
        $this->assertEquals($arg, $data->getEveApiArguments());
    }
    public function testGetEveApiName()
    {
        $data = new EveApiXmlData('test');
        $this->assertEquals('test', $data->getEveApiName());
        $data->setEveApiName('accountList');
        $this->assertEquals('accountList', $data->getEveApiName());
    }
    public function testGetEveApiSectionName()
    {
        $data = new EveApiXmlData('', 'eve');
        $this->assertEquals('eve', $data->getEveApiSectionName());
        $data->setEveApiSectionName('char');
        $this->assertEquals('char', $data->getEveApiSectionName());
    }
    public function testGetEveApiXml()
    {
        $data = new EveApiXmlData();
        $this->assertFalse($data->getEveApiXml());
    }
    public function testHasXmlRowSet()
    {
        $data = new EveApiXmlData();
        $this->assertAttributeEquals('', 'eveApiXml', $data);
        $data->setEveApiXml('<rowset>');
        $this->assertAttributeEquals('<rowset>', 'eveApiXml', $data);
        $this->assertTrue($data->hasXmlRowSet());

    }
}
