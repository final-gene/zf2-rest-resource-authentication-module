<?php
/**
 * rest-resource-authentication-module
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Authentication\Adapter;

use FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter;
use Zend\Authentication\Result;
use Zend\Http\Request;

/**
 * Class AbstractHeaderAuthenticationAdapterTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Authentication\Adapter
 */
class AbstractHeaderAuthenticationAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::setRequest
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::getRequest
     */
    public function testSetAndGetRequest()
    {
        $expected = $this->getMock(Request::class);
        /** @var Request $expected */

        $adapter = $this->getMockForAbstractClass(AbstractHeaderAuthenticationAdapter::class);
        /** @var AbstractHeaderAuthenticationAdapter $adapter */

        $adapter->setRequest($expected);
        $this->assertEquals($expected, $adapter->getRequest());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticate()
    {
        $resultMock = $this->getMockBuilder(Result::class)
            ->setMethods([
                'getCode'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn(Result::FAILURE_UNCATEGORIZED);

        $adapter = $this->getMockBuilder(AbstractHeaderAuthenticationAdapter::class)
            ->setMethods([
                'buildErrorResult'
            ])
            ->getMockForAbstractClass();
        $adapter
            ->expects($this->once())
            ->method('buildErrorResult')
            ->with('No authentication implemented', Result::FAILURE_UNCATEGORIZED)
            ->willReturn($resultMock);
        /** @var AbstractHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_UNCATEGORIZED, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testBuildErrorResult()
    {
        $reflection = new \ReflectionClass(AbstractHeaderAuthenticationAdapter::class);
        $buildErrorResult = $reflection->getMethod('buildErrorResult');
        $buildErrorResult->setAccessible(true);

        $adapter = $this->getMockForAbstractClass(AbstractHeaderAuthenticationAdapter::class);
        $result = $buildErrorResult->invokeArgs($adapter, ['']);
        /** @var Result $result */

        $this->assertInstanceOf(Result::class, $result);
    }
}
