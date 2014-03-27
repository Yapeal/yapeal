<?php
namespace Yapeal\Test;

/**
 * EveApiCacheTest.php
 *
 * PHP version 5.3
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2013-2014 Michael Cummings
 */
use Yapeal\Caching\EveApiCache;

class EveApiCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetsRequiredProperties()
    {
        $ci = $this->getMockBuilder('Yapeal\Util\CachedInterval')
            ->getMock();
        $ci->expects($this->atLeastOnce())
            ->method('getInterval')
            ->will($this->returnValue(300));
        $cache = new EveApiCache('api', 'section', 0, array(), $ci);
        $expected = 'api';
        $this->assertAttributeEquals($expected, 'api', $cache);
    }
}
