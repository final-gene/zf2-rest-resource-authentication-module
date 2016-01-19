<?php
/**
 * Authenticated resource listener test file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Rest;

use FinalGene\RestResourceAuthenticationModule\Authentication\IdentityInterface;
use FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException;
use FinalGene\RestResourceAuthenticationModule\Exception\PermissionException;
use FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener;
use FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService;
use Zend\EventManager\EventManagerInterface;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\Rest\ResourceEvent;

/**
 * Class AuthenticatedResourceListenerTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Rest
 */
class AuthenticatedResourceListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::setAuthenticationService
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::getAuthenticationService
     */
    public function testSetAndGetAuthenticationService()
    {
        $listener = $this->getMockForAbstractClass(AuthenticatedResourceListener::class);
        /** @var AuthenticatedResourceListener $listener */

        $expected = $this->getMock(AuthenticationService::class, [], [], '', false);
        /** @var AuthenticationService $expected */

        $listener->setAuthenticationService($expected);
        $this->assertEquals($expected, $listener->getAuthenticationService());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::attach
     */
    public function testAttach()
    {
        $eventManager = $this->getMock(EventManagerInterface::class, [], [], '', false);
        $listener = $this->getMockForAbstractClass(AuthenticatedResourceListener::class);

        $eventManager
            ->expects($this->any())
            ->method('attach')
            ->withConsecutive(
                ['create', [$listener, 'authenticate'], 10],
                ['delete', [$listener, 'authenticate'], 10],
                ['deleteList', [$listener, 'authenticate'], 10],
                ['fetch', [$listener, 'authenticate'], 10],
                ['fetchAll', [$listener, 'authenticate'], 10],
                ['patch', [$listener, 'authenticate'], 10],
                ['patchList', [$listener, 'authenticate'], 10],
                ['replaceList', [$listener, 'authenticate'], 10],
                ['update', [$listener, 'authenticate'], 10]
            );
        /** @var EventManagerInterface $eventManager */

        /** @var AuthenticatedResourceListener $listener */
        $listener->attach($eventManager);
        $this->assertTrue(true);
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::authenticate
     */
    public function testSuccessfulAuthentication()
    {
        $identity = $this->getMock(IdentityInterface::class);

        $service = $this->getMock(AuthenticationService::class, [], [], '', false);
        $service
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($identity);

        $event = $this->getMock(ResourceEvent::class, [], [], '', false);
        /** @var ResourceEvent $event */

        $listener = $this->getMockForAbstractClass(
            AuthenticatedResourceListener::class,
            [],
            '',
            false,
            false,
            false,
            [
                'getAuthenticationService'
            ]
        );
        $listener
            ->expects($this->once())
            ->method('getAuthenticationService')
            ->willReturn($service);
        /** @var AuthenticatedResourceListener $listener */

        $this->assertInstanceOf(IdentityInterface::class, $listener->authenticate($event));
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::authenticate
     */
    public function testAuthenticationFetchingAuthenticationException()
    {
        $exception = $this->getMock(AuthenticationException::class);
        $exception
            ->expects($this->once())
            ->method('getAuthenticationMessages')
            ->willReturn(['']);
        /** @var AuthenticationException $exception */

        $service = $this->getMock(AuthenticationService::class, [], [], '', false);
        $service
            ->expects($this->once())
            ->method('authenticate')
            ->willThrowException($exception);

        $event = $this->getMock(ResourceEvent::class, [], [], '', false);
        /** @var ResourceEvent $event */

        $listener = $this->getMockForAbstractClass(
            AuthenticatedResourceListener::class,
            [],
            '',
            false,
            false,
            false,
            [
                'getAuthenticationService'
            ]
        );
        $listener
            ->expects($this->once())
            ->method('getAuthenticationService')
            ->willReturn($service);
        /** @var AuthenticatedResourceListener $listener */

        $this->assertInstanceOf(ApiProblemResponse::class, $listener->authenticate($event));
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener::authenticate
     */
    public function testAuthenticationFetchingPermissionException()
    {
        $exception = $this->getMock(PermissionException::class);
        /** @var PermissionException $exception */

        $event = $this->getMock(ResourceEvent::class, [], [], '', false);
        /** @var ResourceEvent $event */

        $identity = $this->getMock(IdentityInterface::class);
        $identity
            ->expects($this->once())
            ->method('checkPermission')
            ->with($event)
            ->willThrowException($exception);

        $service = $this->getMock(AuthenticationService::class, [], [], '', false);
        $service
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($identity);

        $listener = $this->getMockForAbstractClass(
            AuthenticatedResourceListener::class,
            [],
            '',
            false,
            false,
            false,
            [
                'getAuthenticationService'
            ]
        );
        $listener
            ->expects($this->once())
            ->method('getAuthenticationService')
            ->willReturn($service);
        /** @var AuthenticatedResourceListener $listener */

        $this->assertInstanceOf(ApiProblemResponse::class, $listener->authenticate($event));
    }
}
