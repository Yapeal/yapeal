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
 *
 * @property EveApiXmlData EveApiXmlData
 */
class EveApiXmlDataTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->EveApiXmlData = new EveApiXmlData();
    }
    public function testEveApiArguments()
    {
        $this->assertAttributeEmpty('eveApiArguments', $this->EveApiXmlData);
        $arg = array();
        $this->assertEquals($arg, $this->EveApiXmlData->getEveApiArguments());
        $arg = array('arg1' => 'test1', 'arg2' => 'test2');
        $this->EveApiXmlData->setEveApiArguments($arg);
        $this->assertEquals($arg, $this->EveApiXmlData->getEveApiArguments());
    }
    public function testAddEveApiArgumentExceptionValue()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->EveApiXmlData->addEveApiArgument('test', $test);
    }
    public function testAddEveApiArgumentExceptionName()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->EveApiXmlData->addEveApiArgument($test, 'test');
    }
    public function testGetEveApiName()
    {
        $data = new EveApiXmlData('test');
        $this->assertEquals('test', $data->getEveApiName());
        $data->setEveApiName('accountList');
        $this->assertEquals('accountList', $data->getEveApiName());
    }
    public function testGetEveApiNameException()
    {
        $this->setExpectedException('LogicException');
        $this->EveApiXmlData->getEveApiName();
    }
    public function testGetEveApiSectionName()
    {
        $data = new EveApiXmlData('', 'eve');
        $this->assertEquals('eve', $data->getEveApiSectionName());
        $data->setEveApiSectionName('char');
        $this->assertEquals('char', $data->getEveApiSectionName());
    }
    public function testGetEveApiSectionNameException()
    {
        $this->setExpectedException('LogicException');
        $this->EveApiXmlData->getEveApiSectionName();
    }
    public function testGetEveApiXml()
    {
        $this->assertFalse($this->EveApiXmlData->getEveApiXml());
    }
    public function testHasXmlRowSet()
    {
        $this->assertAttributeEquals('', 'eveApiXml', $this->EveApiXmlData);
        $this->assertFalse($this->EveApiXmlData->hasXmlRowSet());
        $this->EveApiXmlData->setEveApiXml('<rowset>');
        $this->assertAttributeEquals(
             '<rowset>',
                 'eveApiXml',
                 $this->EveApiXmlData
        );
        $this->assertTrue($this->EveApiXmlData->hasXmlRowSet());
    }
    public function testToString()
    {
        $this->assertAttributeEquals('', 'eveApiXml', $this->EveApiXmlData);
        //$this->assertEquals('', (string) $this->EveApiXmlData);
        $data = new EveApiXmlData('', 'eve', array(), 'test');
        $this->assertEquals('test', $data);
    }
    public function testSetEveApiNameException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->EveApiXmlData->setEveApiName($test);
    }
    public function testSetEveApiSectionNameException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->EveApiXmlData->setEveApiSectionName($test);
    }
    public function testSetEveApiXmlException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->EveApiXmlData->setEveApiXml($test);
    }
}
