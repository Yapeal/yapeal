<?php
/**
 * Contains EveApiXmlDataTest class.
 *
 * PHP version 5.4
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
    /**
     *
     */
    public function setUp()
    {
        $this->data = new EveApiXmlData();
    }
    /**
     *
     */
    public function testAddEveApiArgumentExceptionName()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->data->addEveApiArgument($test, 'test');
    }
    /**
     *
     */
    public function testEveApiArguments()
    {
        $this->assertAttributeEmpty('eveApiArguments', $this->data);
        $arg = array();
        $this->assertEquals($arg, $this->data->getEveApiArguments());
        $arg = array('arg1' => 'test1', 'arg2' => 'test2');
        $this->data->setEveApiArguments($arg);
        $this->assertEquals($arg, $this->data->getEveApiArguments());
    }
    /**
     *
     */
    public function testGetEveApiName()
    {
        $data = new EveApiXmlData('test');
        $this->assertEquals('test', $data->getEveApiName());
        $data->setEveApiName('accountList');
        $this->assertEquals('accountList', $data->getEveApiName());
    }
    /**
     *
     */
    public function testGetEveApiNameException()
    {
        $this->setExpectedException('LogicException');
        $this->data->getEveApiName();
    }
    /**
     *
     */
    public function testGetEveApiSectionName()
    {
        $this->data->setEveApiSectionName('eve');
        $this->assertEquals('eve', $this->data->getEveApiSectionName());
        $this->data->setEveApiSectionName('char');
        $this->assertEquals('char', $this->data->getEveApiSectionName());
    }
    /**
     *
     */
    public function testGetEveApiSectionNameException()
    {
        $this->setExpectedException('LogicException');
        $this->data->getEveApiSectionName();
    }
    /**
     *
     */
    public function testGetEveApiXml()
    {
        $this->assertFalse($this->data->getEveApiXml());
    }
    /**
     *
     */
    public function testSetEveApiNameException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->data->setEveApiName($test);
    }
    /**
     *
     */
    public function testSetEveApiSectionNameException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->data->setEveApiSectionName($test);
    }
    /**
     *
     */
    public function testSetEveApiXmlException()
    {
        $test = (int)20;
        $this->setExpectedException('InvalidArgumentException');
        $this->data->setEveApiXml($test);
    }
    /**
     *
     */
    public function testToString()
    {
        $this->assertAttributeEquals('', 'eveApiXml', $this->data);
        $this->assertEquals('', (string)$this->data);
        $result = new EveApiXmlData('', 'eve', array(), 'test');
        $this->assertEquals('test', $result);
    }
    /**
     * @type EveApiXmlData
     */
    protected $data;
}
