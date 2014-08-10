<?php
/**
 * Contains FileCacheRetrieverTest class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
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

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;
use Yapeal\Xml\FileCacheRetriever;

/**
 * Class FileCacheRetrieverTest
 */
class FileCacheRetrieverTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setup()
    {
        $this->logger = $this->getLoggerMock();
        $this->retriever = new FileCacheRetriever($this->logger, '');
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForAboveRootPath()
    {
        $dataMock = $this->getDataMock();
        $before = $dataMock->getEveApiXml();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('debug')
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
        $this->retriever->setCachePath($input)
                        ->retrieveEveApi($dataMock);
        $this->assertAttributeEquals($input, 'cachePath', $this->retriever);
        $this->assertSame($before, $dataMock->getEveApiXml());
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForNonDir()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
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
        $this->retriever->setCachePath($input)
                        ->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForNonExistingPath()
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
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForNonReadableFile()
    {
        $dataMock = $this->getDataMock();
        $filesystem = $this->getVfsStream();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
        $this->assertTrue(
            $filesystem->hasChild('yapealTest/cache/account/deniedReadable')
        );
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiSectionName')
                 ->will($this->returnValue('account'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiName')
                 ->will($this->returnValue('deniedReadable'));
        $dataMock->expects($this->atLeastOnce())
                 ->method('getEveApiArguments')
                 ->will($this->returnValue(array('dummy' => 'amount')));
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Could NOT find accessible cache file was given '
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
        $this->retriever->setCachePath($input)
                        ->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForNonReadablePath()
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
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorForRelativePath()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
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
        $this->retriever->setCachePath($input)
                        ->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorWhenCachePathNotSet()
    {
        $dataMock = $this->getDataMock();
        $this->assertAttributeEmpty('cachePath', $this->retriever);
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
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApiLogsErrorWhenCanNotGetLock()
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
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->callback(
                    function (
                        $subject,
                        $message = 'Giving up could NOT get flock on '
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
        $lock =
            $filesystem->url() . '/cache/account/test' . $hash . '.xml';
        $handle = fopen($lock, 'ab+');
        flock($handle, LOCK_EX);
        $this->retriever->retrieveEveApi($dataMock);
        flock($handle, LOCK_UN);
        fclose($handle);
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
     * @return EveApiXmlModifyInterface|PHPUnit_Framework_MockObject_MockObject
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
    /**
     * @type LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;
    /**
     * @type FileCacheRetriever
     */
    protected $retriever;
}
