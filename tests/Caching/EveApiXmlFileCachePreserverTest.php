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
namespace Caching;

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
        $this->setDataMock();
    }
    /**
     *
     */
    public function testDestructor()
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->preserver = null;
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsLogicExceptionWhenCachePathNotSet()
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException('\LogicException');
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonDir()
    {
        $this->setupVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $this->filesystem->hasChild('yapealTest/cache/NotDir')
        );
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('NotDir'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            2
        );
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonExistingPath(
    )
    {
        $this->setupVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $this->filesystem->hasChild('yapealTest/cache')
        );
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('DoesNotExist'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            1
        );
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonReadablePath(
    )
    {
        $this->setupVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $this->filesystem->hasChild('yapealTest/cache')
        );
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('deniedRead'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            1
        );
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverExceptionForNonWritablePath(
    )
    {
        $this->setupVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue(
            $this->filesystem->hasChild('yapealTest/cache/deniedWrite')
        );
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('deniedWrite'));
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverException',
            null,
            3
        );
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverPathExceptionForAboveRootPath(
    )
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverPathException',
            null,
            1
        );
        $input = '/good/gone/../../../bad/';
        $this->preserver->setCachePath($input);
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserveEveApiThrowsYapealPreserverPathExceptionForRelativePath(
    )
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverPathException',
            null,
            1
        );
        $input = 'no/root/';
        $this->preserver->setCachePath($input);
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserverEveApiThrowsYapealPreserverFileExceptionWhenCanNotGetLock(
    )
    {
        $this->setupVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($this->filesystem->hasChild($xml));
        $this->assertTrue($this->filesystem->hasChild($tmp));
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('account'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiName')
                       ->will($this->returnValue('test'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiArguments')
                       ->will($this->returnValue(array('dummy' => 'amount')));
        $lock =
            $this->filesystem->url() . '/cache/account/test' . $hash . '.tmp';
        $handle = fopen($lock, 'rb+');
        flock($handle, LOCK_EX);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverFileException',
            null,
            2
        );
        $this->preserver->preserveEveApi($this->dataMock);
        flock($handle, LOCK_UN);
        fclose($handle);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserverEveApiThrowsYapealPreserverFileExceptionWhenCanNotWriteTmp(
    )
    {
        $this->setupVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($this->filesystem->hasChild($xml));
        $this->assertTrue($this->filesystem->hasChild($tmp));
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('account'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiName')
                       ->will($this->returnValue('test'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiArguments')
                       ->will($this->returnValue(array('dummy' => 'amount')));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiXml')
                       ->will($this->returnValue('Not XML'));
        $this->filesystem->getChild($tmp)
                         ->chmod(0444);
        $this->setExpectedException(
            '\Yapeal\Exception\YapealPreserverFileException',
            null,
            1
        );
        $this->preserver->preserveEveApi($this->dataMock);
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserverEveApiWritesFile()
    {
        $this->setupVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($this->filesystem->hasChild($xml));
        $this->assertTrue($this->filesystem->hasChild($tmp));
        $input = $this->filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('account'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiName')
                       ->will($this->returnValue('test'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiArguments')
                       ->will($this->returnValue(array('dummy' => 'amount')));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiXml')
                       ->will($this->returnValue('Not XML'));
        $this->preserver->preserveEveApi($this->dataMock);
        $this->assertFalse($this->filesystem->hasChild($tmp));
        $this->assertTrue($this->filesystem->hasChild($xml));
        $this->preserver->preserveEveApi($this->dataMock);
        $this->assertFalse($this->filesystem->hasChild($tmp));
        $this->assertTrue($this->filesystem->hasChild($xml));
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testPreserverEveApiWritesFileWithIndirectPath()
    {
        $this->setupVfsStream();
        $hash = '98427c308f8b8d734b659ce1830ae006';
        $xml = 'yapealTest/cache/account/test' . $hash . '.xml';
        $tmp = 'yapealTest/cache/account/test' . $hash . '.tmp';
        $this->assertFalse($this->filesystem->hasChild($xml));
        $this->assertTrue($this->filesystem->hasChild($tmp));
        $input = $this->filesystem->url() . '//cache/./';
        $this->preserver->setCachePath($input);
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiSectionName')
                       ->will($this->returnValue('account'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiName')
                       ->will($this->returnValue('test'));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiArguments')
                       ->will($this->returnValue(array('dummy' => 'amount')));
        $this->dataMock->expects($this->atLeastOnce())
                       ->method('getEveApiXml')
                       ->will($this->returnValue('Not XML'));
        $this->preserver->preserveEveApi($this->dataMock);
        $this->assertFalse($this->filesystem->hasChild($tmp));
        $this->assertTrue($this->filesystem->hasChild($xml));
        $this->preserver->preserveEveApi($this->dataMock);
        $this->assertFalse($this->filesystem->hasChild($tmp));
        $this->assertTrue($this->filesystem->hasChild($xml));
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testSetCachePath()
    {
        $this->setupVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $input = $this->filesystem->url() . '/cache/account/';
        $this->preserver->setCachePath($input);
        $this->assertAttributeEquals($input, 'cachePath', $this->preserver);
        $this->filesystem = null;
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
        $this->filesystem = null;
    }
    /**
     *
     */
    public function testSetCachePathWithNullValueUsesDefaultPath()
    {
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $input = null;
        $this->preserver->setCachePath($input);
        $expect = dirname(dirname(__DIR__)) . '/cache/';
        $this->assertAttributeEquals($expect, 'cachePath', $this->preserver);
        $this->filesystem = null;
    }
    /**
     * @var EveApiXmlDataInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;
    /**
     * @var vfsStreamDirectory
     */
    protected $filesystem;
    /**
     * @var EveApiXmlFileCachePreserver
     */
    protected $preserver;
    /**
     *
     */
    protected function setDataMock()
    {
        $this->dataMock = $this->getMockBuilder('Yapeal\Xml\EveApiXmlData')
                               ->disableOriginalConstructor()
                               ->getMock();
    }
    /**
     * @throws \InvalidArgumentException
     */
    protected function setupVfsStream()
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
        $this->filesystem = vfsStream::setup('yapealTest');
        vfsStream::create($structure, $this->filesystem);
        $this->filesystem->getChild('yapealTest/cache/deniedRead')
                         ->chmod(0222);
        $this->filesystem->getChild('yapealTest/cache/deniedWrite')
                         ->chmod(0444);
    }
}
