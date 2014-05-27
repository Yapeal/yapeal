<?php
/**
 * Contains GuzzleNetworkRetrieverTest class.
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
namespace Test\Network;

use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use Yapeal\Network\GuzzleNetworkRetriever;
use Guzzle\Common\Exception\GuzzleException;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class GuzzleNetworkRetrieverTest
 */
class GuzzleNetworkRetrieverTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testRetrieveEveApi()
    {
        $mockResponse = $this->getResponseMock();
        $mockRequest = $this->getRequestMock($this->returnValue($mockResponse));
        $mockClient = $this->getClientMock($mockRequest);
        $retriever = new GuzzleNetworkRetriever($this->getLoggerMock(), $mockClient);
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
        $retriever->retrieveEveApi($dataMock);
    }

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
     * @expectedException /Exception
     */
    public function testReadXmlDataGuzzleException()
    {
        $mockRequest = $this->getRequestMock($this->throwException(new \Exception('test')));
        $mockClient = $this->getClientMock($mockRequest);
        $retriever = new GuzzleNetworkRetriever($this->getLoggerMock(), $mockClient);
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
        $retriever->retrieveEveApi($dataMock);
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponseMock() {
        $mockResponse = $this->getMockBuilder('\Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $mockResponse->expects($this->atLeastOnce())
                     ->method('getBody')
                     ->will($this->returnValue('Not XML'));

        return $mockResponse;
    }
    /**
     * @param $mockResponse
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestMock($mockResponse)
    {
        $mockRequest =$this->getMockBuilder('\Guzzle\Http\Message\RequestInterface')
                            ->disableOriginalConstructor()
                            ->getMock();
        $mockRequest->expects($this->atLeastOnce())
                    ->method('send')
                    ->will($mockResponse);
        return $mockRequest;
    }
    /**
     * @param $mockRequest
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock($mockRequest)
    {
        $mockClient = $this->getMockBuilder('\Guzzle\Http\ClientInterface')
                           ->getMock();
        $mockClient->expects($this->atLeastOnce())
                   ->method('post')
                   ->will($this->returnValue($mockRequest));
        return $mockClient;
    }
}
