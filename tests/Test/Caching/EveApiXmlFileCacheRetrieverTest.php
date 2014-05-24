<?php
/**
 * Contains EveApiXmlFileCacheRetrieverTest class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
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
namespace Yapeal\Test\Caching;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Yapeal\Caching\EveApiXmlFileCacheRetriever;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class EveApiXmlFileCacheRetrieverTest
 */
class EveApiXmlFileCacheRetrieverTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setup()
    {
        $this->retriever = new EveApiXmlFileCacheRetriever('');
    }
    /**
     *
     */
    public function testRetrieveEveApiReadsFile()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $this->assertTrue($filesystem->hasChild($xml));
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('account'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiName')
                 ->will($this->returnValue('test'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiArguments')
                 ->will($this->returnValue(array('dummy' => 'amount')));
        $dataMock->expects($this->atLeastOnce())
                 ->method('setEveApiXml')
                 ->with('Not XML');
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiReadsFileWithIndirectPath()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $this->assertTrue($filesystem->hasChild($xml));
        $input = $filesystem->url() . '//cache/./';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('account'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiName')
                 ->will($this->returnValue('test'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiArguments')
                 ->will($this->returnValue(array('dummy' => 'amount')));
        $dataMock->expects($this->atLeastOnce())
                 ->method('setEveApiXml')
                 ->with('Not XML');
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsLogicExceptionWhenCachePathNotSet()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->setExpectedException('\LogicException');
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsYapealRetrieverFileExceptionForNonReadableFile(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache/account/deniedReadable')
        );
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('account'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiName')
                 ->will($this->returnValue('deniedReadable'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiArguments')
                 ->will($this->returnValue(array('dummy' => 'amount')));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverFileException',
            'Could NOT find accessible cache file was given ',
            1
        );
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsYapealRetrieverFileExceptionWhenCanNotGetLock(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $this->assertTrue($filesystem->hasChild($xml));
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('account'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiName')
                 ->will($this->returnValue('test'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiArguments')
                 ->will($this->returnValue(array('dummy' => 'amount')));
        $lock =
            $filesystem->url() . '/cache/account/test' . $hash . '.xml';
        $handle = fopen($lock, 'ab+');
        flock($handle, LOCK_EX);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverFileException',
            'Giving up could NOT get flock on ',
            1
        );
        $this->retriever->retrieveEveApi($dataMock);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    public function testRetrieveEveApiThrowsYapealRetrieverPathExceptionForAboveRootPath(
    )
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverPathException',
            null,
            1
        );
        $input = '/good/gone/../../../bad/';
        $this->retriever->setCachePath($input);
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsYapealRetrieverPathExceptionForNonDir(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache/NotDir'));
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('NotDir'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverPathException',
            null,
            2
        );
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsYapealRetrieverPathExceptionForNonExistingPath(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache'));
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('DoesNotExist'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverPathException',
            null,
            1
        );
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiThrowsYapealRetrieverPathExceptionForNonReadablePath(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache'));
        $input = $filesystem->url() . '/cache';
        $this->retriever->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('deniedRead'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverPathException',
            null,
            1
        );
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function
    testRetrieveEveApiThrowsYapealRetrieverPathExceptionForRelativePath()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealRetrieverPathException',
            null,
            1
        );
        $input = 'no/root/';
        $this->retriever->setCachePath($input);
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testSetCachePath()
    {
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $input = $filesystem->url() . '/cache/account/';
        $this->retriever->setCachePath($input);
        $this->assertAttributeEquals($input, 'cachePath', $this->retriever);
    }
    /**
     *
     */
    public function testSetCachePathThrowsInvalidArgumentExceptionForIncorrectType(
    )
    {
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $input = 123;
        $this->setExpectedException('InvalidArgumentException');
        $this->retriever->setCachePath($input);
    }
    /**
     *
     */
    public function testSetCachePathWithNullValueUsesDefaultPath()
    {
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $input = null;
        $this->retriever->setCachePath($input);
        $expect = dirname(dirname(dirname(__DIR__))) . '/cache/';
        $this->assertAttributeEquals($expect, 'cachePath', $this->retriever);
    }
    /**
     * @var EveApiXmlFileCacheRetriever
     */
    protected $retriever;
    /**
     * @return EveApiXmlDataInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMock()
    {
        $dataMock = $this->getMockBuilder('Yapeal\Xml\EveApiXmlData')
                         ->disableOriginalConstructor()
                         ->getMock();
        return $dataMock;
    }
    /**
     * @throws \InvalidArgumentException
     * @return vfsStreamDirectory
     */
    protected function getVfsStream()
    {
        $structure = array(
            'cache' => array(
                'account' => array(
                    'test98427c308f8b8d734b659ce1830ae006.xml' => 'Not XML',
                    'notFile' => array(),
                    'deniedReadable' => 'Not XML'
                ),
                'char' => array(),
                'deniedRead' => array(),
                'NotDir' => ''
            )
        );
        $filesystem = vfsStream::setup('yapealTest');
        vfsStream::create($structure, $filesystem);
        $filesystem->getChild('yapealTest/cache/deniedRead')
                   ->chmod(0333);
        $filesystem->getChild('yapealTest/cache/account/deniedReadable')
                   ->chmod(0333);
        return $filesystem;
    }
}
