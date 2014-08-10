<?php
/**
 * Contains GuzzleNetworkRetrieverTest class.
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

use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;
use Yapeal\Xml\GuzzleNetworkRetriever;

/**
 * Class GuzzleNetworkRetrieverTest
 */
class GuzzleNetworkRetrieverTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->logger = $this->getLoggerMock();
        $this->response = $this->getResponseMock();
        $this->request = $this->getRequestMock();
        $this->client = $this->getClientMock($this->request);
        $this->retriever =
            new GuzzleNetworkRetriever($this->logger, $this->client);
    }
    /**
     *
     */
    public function testReadXmlDataLogsErrorForUnableToReceive()
    {
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Could NOT get XML data',
                $this->anything()
            );
        $this->request->expects($this->atLeastOnce())
                      ->method('send')
                      ->will(
                          $this->throwException(new RequestException('test'))
                      );
        $this->client->expects($this->atLeastOnce())
                     ->method('post')
                     ->will($this->returnValue($this->request));
        $dataMock = $this->getDataMock();
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
                 ->with(false);
        $this->retriever->retrieveEveApi($dataMock);
    }
    /**
     *
     */
    public function testRetrieveEveApi()
    {
        $this->response->expects($this->atLeastOnce())
                       ->method('getBody')
                       ->will($this->returnValue('Not XML'));
        $this->request->expects($this->atLeastOnce())
                      ->method('send')
                      ->will($this->returnValue($this->response));
        $this->client->expects($this->atLeastOnce())
                     ->method('post')
                     ->will($this->returnValue($this->request));
        $dataMock = $this->getDataMock();
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
     * @throws \PHPUnit_Framework_Exception
     * @return \Guzzle\Http\ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $mockClient = $this->getMockBuilder('\Guzzle\Http\ClientInterface')
                           ->getMock();
        return $mockClient;
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
     * @throws \PHPUnit_Framework_Exception
     * @return RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestMock()
    {
        $mockRequest =
            $this->getMockBuilder('\Guzzle\Http\Message\RequestInterface')
                 ->disableOriginalConstructor()
                 ->getMock();
        return $mockRequest;
    }
    /**
     * @throws \PHPUnit_Framework_Exception
     * @return Response|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponseMock()
    {
        $mockResponse = $this->getMockBuilder('\Guzzle\Http\Message\Response')
                             ->disableOriginalConstructor()
                             ->getMock();
        return $mockResponse;
    }
    /**
     * @type \Guzzle\Http\ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;
    /**
     * @type LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;
    /**
     * @type RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;
    /**
     * @type Response|PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;
    /**
     * @var GuzzleNetworkRetriever
     */
    protected $retriever;
}
