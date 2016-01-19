<?php
/**
 * rest-resource-authentication-module
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Service;

use FinalGene\RestResourceAuthenticationModule\Authentication\IdentityInterface;
use FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

/**
 * Class AuthenticationServiceTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Service
 */
class AuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService::setAdapter
     * @covers FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService::getAdapter
     */
    public function testSetAndGetAdapter()
    {
        $service = new AuthenticationService();

        $expected = $this->getMock(AdapterInterface::class, [], [], '', false);
        /** @var AdapterInterface $expected */

        $service->setAdapter($expected);
        $this->assertEquals($expected, $service->getAdapter());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService::authenticate
     */
    public function testSuccessfulAuthentication()
    {
        $identity = $this->getMock(IdentityInterface::class);

        $result = $this->getMock(Result::class, [], [], '', false);
        $result
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $result
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);
        /** @var Result $result */

        $adapter = $this->getMock(AdapterInterface::class, [], [], '', false);
        $adapter
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($result);
        /** @var AdapterInterface $adapter */

        $service = $this->getMock(AuthenticationService::class, ['getAdapter'], [], '', false);
        $service
            ->expects($this->once())
            ->method('getAdapter')
            ->willReturn($adapter);
        /** @var AuthenticationService $service */

        $this->assertInstanceOf(IdentityInterface::class, $service->authenticate());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException
     * @expectedException \FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException
     */
    public function testAuthenticationWillThrowException()
    {
        $result = $this->getMock(Result::class, [], [], '', false);
        $result
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $result
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);
        /** @var Result $result */

        $adapter = $this->getMock(AdapterInterface::class, [], [], '', false);
        $adapter
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($result);
        /** @var AdapterInterface $adapter */

        $service = $this->getMock(AuthenticationService::class, ['getAdapter'], [], '', false);
        $service
            ->expects($this->once())
            ->method('getAdapter')
            ->willReturn($adapter);
        /** @var AuthenticationService $service */

        $service->authenticate();
    }
}
