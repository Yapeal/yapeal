<?php
/**
 * Contains EveApiXmlFileCachePreserverTest class.
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
use Yapeal\Caching\EveApiXmlFileCachePreserver;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class EveApiXmlFileCachePreserverTest
 */
class EveApiXmlFileCachePreserverTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setup()
    {
        $this->preserver = new EveApiXmlFileCachePreserver('');
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsLogicExceptionWhenCachePathNotSet()
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException('\LogicException');
        $this->preserver->preserveEveApi($this->getDataMock());
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonDir()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache/NotDir')
        );
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('NotDir'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            2
        );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonExistingPath(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache')
        );
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('DoesNotExist'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            1
        );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonReadablePath(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache')
        );
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('deniedRead'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            1
        );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonWritablePath(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache/deniedWrite')
        );
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('deniedWrite'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            3
        );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverFileExceptionWhenCanNotGetLock(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($filesystem->hasChild($xml));
        $this->assertTrue($filesystem->hasChild($tmp));
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
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
            $filesystem->url() . '/cache/account/test' . $hash . '.tmp';
        $handle = fopen($lock, 'rb+');
        flock($handle, LOCK_EX);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverFileException',
            null,
            2
        );
        $this->preserver->preserveEveApi($dataMock);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverFileExceptionWhenCanNotWriteTmp(
    )
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($filesystem->hasChild($xml));
        $this->assertTrue($filesystem->hasChild($tmp));
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
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
                 ->method('getEveApiXml')
                 ->will($this->returnValue('Not XML'));
        $filesystem->getChild($tmp)
                   ->chmod(0444);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverFileException',
            null,
            1
        );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverPathExceptionForAboveRootPath(
    )
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverPathException',
            null,
            1
        );
        $input = '/good/gone/../../../bad/';
        $this->preserver->setCachePath($input);
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverPathExceptionForRelativePath(
    )
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverPathException',
            null,
            1
        );
        $input = 'no/root/';
        $this->preserver->setCachePath($input);
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiWritesFile()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($filesystem->hasChild($xml));
        $this->assertTrue($filesystem->hasChild($tmp));
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
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
                 ->method('getEveApiXml')
                 ->will($this->returnValue('Not XML'));
        $this->preserver->preserveEveApi($dataMock);
        $this->assertFalse($filesystem->hasChild($tmp));
        $this->assertTrue($filesystem->hasChild($xml));
        $this->preserver->preserveEveApi($dataMock);
        $this->assertFalse($filesystem->hasChild($tmp));
        $this->assertTrue($filesystem->hasChild($xml));
    }
    /**
     *
     */
    public function testPreserveEveApiWritesFileWithIndirectPath()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($filesystem->hasChild($xml));
        $this->assertTrue($filesystem->hasChild($tmp));
        $input = $filesystem->url() . '//cache/./';
        $this->preserver->setCachePath($input);
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
                 ->method('getEveApiXml')
                 ->will($this->returnValue('Not XML'));
        $this->preserver->preserveEveApi($dataMock);
        $this->assertFalse($filesystem->hasChild($tmp));
        $this->assertTrue($filesystem->hasChild($xml));
        $this->preserver->preserveEveApi($dataMock);
        $this->assertFalse($filesystem->hasChild($tmp));
        $this->assertTrue($filesystem->hasChild($xml));
    }
    /**
     *
     */
    public function testSetCachePath()
    {
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $input = $filesystem->url() . '/cache/account/';
        $this->preserver->setCachePath($input);
        $this->assertAttributeEquals($input, 'cachePath', $this->preserver);
    }
    /**
     *
     */
    public function testSetCachePathThrowsInvalidArgumentExceptionForIncorrectType(
    )
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $input = 123;
        $this->setExpectedException('InvalidArgumentException');
        $this->preserver->setCachePath($input);
    }
    /**
     *
     */
    public function testSetCachePathWithNullValueUsesDefaultPath()
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $input = null;
        $this->preserver->setCachePath($input);
        $expect = dirname(dirname(dirname(__DIR__))) . '/cache/';
        $this->assertAttributeEquals($expect, 'cachePath', $this->preserver);
    }
    /**
     * @var EveApiXmlFileCachePreserver
     */
    protected $preserver;
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
                    'test98427c308f8b8d734b659ce1830ae006.tmp' => 'Not XML'
                ),
                'char' => array(),
                'deniedRead' => array(),
                'deniedWrite' => array(),
                'NotDir' => ''
            )
        );
        $filesystem = vfsStream::setup('yapealTest');
        vfsStream::create($structure, $filesystem);
        $filesystem->getChild('yapealTest/cache/deniedRead')
                   ->chmod(0333);
        $filesystem->getChild('yapealTest/cache/deniedWrite')
                   ->chmod(0555);
        return $filesystem;
    }
}
