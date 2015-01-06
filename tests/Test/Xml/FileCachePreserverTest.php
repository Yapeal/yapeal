<?php
/**
 * Contains FileCachePreserverTest class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Test\Xml;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Yapeal\Xml\EveApiReadInterface;
use Yapeal\Xml\FileCachePreserver;

/**
 * Class FileCachePreserverTest
 */
class FileCachePreserverTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setup()
    {
        $this->logger = $this->getLoggerMock();
        $this->preserver = new FileCachePreserver($this->logger, '');
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorForNonDir()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache/NotDir'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('NotDir'));
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Cache path is NOT a directory was given '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input)
                        ->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorForNonExistingPath()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache'));
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('DoesNotExist'));
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Cache path is NOT readable or does NOT exist was given '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorForNonReadablePath()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->assertTrue($filesystem->hasChild('yapealTest/cache'));
        $input = $filesystem->url() . '/cache';
        $this->preserver->setCachePath($input);
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('deniedRead'));
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Cache path is NOT readable or does NOT exist was given '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorForNonWritablePath()
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
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Cache path is NOT writable was given '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorForRelativePath()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Path NOT absolute missing drive or root was given '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $input = 'no/root/';
        $this->preserver->setCachePath($input)
                        ->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorWhenCachePathNotSet()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Tried to access $cachePath before it was set'
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if ($exception->getMessage()
                                == $message
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $this->preserver->preserveEveApi($dataMock);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorWhenCanNotGetLock()
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
            ->will($this->returnValue(['dummy' => 'amount']));
//        $this->logger
//            ->expects($this->atLeastOnce())
//            ->method('info')
//            ->with(
//                'Could NOT get XML data',
//                $this->callback(
//                    function (
//                        $subject,
//                        $message = 'Giving up could NOT get flock on '
//                    ) {
//                        /**
//                         * @type array $subject
//                         */
//                        if (isset($subject['exception'])) {
//                            /** @type \Exception $exception */
//                            $exception = $subject['exception'];
//                            if (false !== strpos(
//                                    $exception->getMessage(),
//                                    $message
//                                )
//                            ) {
//                                return true;
//                            }
//                        }
//                        return false;
//                    }
//                )
//            );
        $lock =
            $filesystem->url() . '/cache/account/test' . $hash . '.tmp';
        $handle = fopen($lock, 'rb+');
        flock($handle, LOCK_EX);
        $this->preserver->preserveEveApi($dataMock);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    /**
     *
     */
    public function testPreserveEveApiLogsErrorWhenCanNotWriteTmp()
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
            ->will($this->returnValue(['dummy' => 'amount']));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiXml')
                 ->will($this->returnValue('Not XML'));
        $filesystem->getChild($tmp)
                   ->chmod(0444);
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Giving up could NOT finish writing '
                    ) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    $message
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
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
            ->will($this->returnValue(['dummy' => 'amount']));
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
            ->will($this->returnValue(['dummy' => 'amount']));
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
    public function testRetrieveEveApiLogsErrorForAboveRootPath()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->preserver);
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function ($subject) {
                        /**
                         * @type array $subject
                         */
                        if (isset($subject['exception'])) {
                            /** @type \Exception $exception */
                            $exception = $subject['exception'];
                            if (false !== strpos(
                                    $exception->getMessage(),
                                    'Can NOT go above root path but given '
                                )
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        $input = '/good/gone/../../../bad/';
        $this->assertSame(
            $dataMock,
            $this->preserver->setCachePath($input)
                            ->preserveEveApi($dataMock)
        );
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
     * @return EveApiReadInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMock()
    {
        $dataMock = $this->getMockBuilder('Yapeal\Xml\EveApiXmlData')
                         ->disableOriginalConstructor()
                         ->getMock();
        return $dataMock;
    }
    /**
     * @return LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerMock()
    {
        $loggerMock = $this->getMockBuilder('Psr\Log\NullLogger')
                           ->disableOriginalConstructor()
                           ->getMock();
        return $loggerMock;
    }
    /**
     * @throws \InvalidArgumentException
     * @return vfsStreamDirectory
     */
    protected function getVfsStream()
    {
        $structure = [
            'cache' => [
                'account' => [
                    'test98427c308f8b8d734b659ce1830ae006.tmp' => 'Not XML'
                ],
                'char' => [],
                'deniedRead' => [],
                'deniedWrite' => [],
                'NotDir' => ''
            ]
        ];
        $filesystem = vfsStream::setup('yapealTest');
        vfsStream::create($structure, $filesystem);
        $filesystem->getChild('yapealTest/cache/deniedRead')
                   ->chmod(0333);
        $filesystem->getChild('yapealTest/cache/deniedWrite')
                   ->chmod(0555);
        return $filesystem;
    }
    /**
     * @type LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;
    /**
     * @var FileCachePreserver
     */
    protected $preserver;
}
