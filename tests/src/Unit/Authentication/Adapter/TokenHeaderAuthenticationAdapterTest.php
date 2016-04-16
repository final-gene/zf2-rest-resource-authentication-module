<?php
/**
 * Token header authentication adapter test file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Authentication\Adapter;

use FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter;
use FinalGene\RestResourceAuthenticationModule\Authentication\IdentityInterface;
use FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException;
use FinalGene\RestResourceAuthenticationModule\Exception\TokenException;
use FinalGene\RestResourceAuthenticationModule\Service\IdentityServiceInterface;
use Zend\Authentication\Result;
use Zend\Http\Header\HeaderInterface;
use Zend\Http\Headers;
use Zend\Http\Request;

/**
 * Class TokenHeaderAuthenticationAdapterTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Authentication\Adapter
 */
class TokenHeaderAuthenticationAdapterTest extends \PHPUnit_Framework_TestCase
{
    const PUBLIC_STRING = 'buz';
    const SECRET_STRING = 'bar';
    const REQUEST_STRING = 'foo';
    const SIGNATURE_STRING = '147933218aaabc0b8b10a2b3a5c34684c8d94341bcf10a4736dc7270f7741851';

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::setIdentityService
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::getIdentityService
     */
    public function testSetAndGetIdentityService()
    {
        $expectedIdentityService = $this->getMock(IdentityServiceInterface::class);
        /** @var IdentityServiceInterface $expectedIdentityService */

        $adapter = new TokenHeaderAuthenticationAdapter();

        $adapter->setIdentityService($expectedIdentityService);
        $this->assertEquals($expectedIdentityService, $adapter->getIdentityService());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::getHmac
     */
    public function testGetHmac()
    {
        $header = $this->getMock(HeaderInterface::class);

        $headers = $this->getMock(
            Headers::class,
            [
                'clearHeaders',
            ]
        );
        $headers
            ->expects($this->once())
            ->method('clearHeaders');

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
                'setHeaders',
                'toString',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        $request
            ->expects($this->once())
            ->method('setHeaders')
            ->with($headers);
        $request
            ->expects($this->once())
            ->method('toString')
            ->willReturn(self::REQUEST_STRING);
        /** @var Request $request */

        $getHmac = $this->getMethod('getHmac');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $hmac = $getHmac->invokeArgs($adapter, [$request, self::SECRET_STRING]);

        $this->assertEquals(self::SIGNATURE_STRING, $hmac);
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractSignature
     */
    public function testExtractSignature()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $extractSignature = $this->getMethod('extractSignature');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $signature = $extractSignature->invokeArgs($adapter, [$authorization]);

        $this->assertEquals(self::SIGNATURE_STRING, $signature);
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractSignature
     * @expectedException \FinalGene\RestResourceAuthenticationModule\Exception\TokenException
     */
    public function testExtractInvalidSignature()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING;

        $extractSignature = $this->getMethod('extractSignature');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $extractSignature->invokeArgs($adapter, [$authorization]);
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractPublicKey
     */
    public function testExtractPublicKey()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $extractPublicKey = $this->getMethod('extractPublicKey');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $signature = $extractPublicKey->invokeArgs($adapter, [$authorization]);

        $this->assertEquals(self::PUBLIC_STRING, $signature);
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractPublicKey
     * @expectedException \FinalGene\RestResourceAuthenticationModule\Exception\TokenException
     */
    public function testExtractInvalidPublicKey()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING;

        $extractPublicKey = $this->getMethod('extractPublicKey');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $extractPublicKey->invokeArgs($adapter, [$authorization]);
    }

    /**
     * @param $methodName
     *
     * @return \ReflectionMethod
     */
    private function getMethod($methodName)
    {
        $reflection = new \ReflectionClass(TokenHeaderAuthenticationAdapter::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testSuccessfulAuthentication()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $identity = $this->getMock(IdentityInterface::class);
        $identity
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn(self::SECRET_STRING);

        $identityService = $this->getMock(IdentityServiceInterface::class);
        $identityService
            ->expects($this->once())
            ->method('getIdentity')
            ->with(self::PUBLIC_STRING)
            ->willReturn($identity);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
                'extractSignature',
                'getIdentityService',
                'getHmac',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->with($authorization)
            ->willReturn(self::PUBLIC_STRING);
        $adapter
            ->expects($this->once())
            ->method('extractSignature')
            ->with($authorization)
            ->willReturn(self::SIGNATURE_STRING);
        $adapter
            ->expects($this->once())
            ->method('getIdentityService')
            ->willReturn($identityService);
        $adapter
            ->expects($this->once())
            ->method('getHmac')
            ->with($request, self::SECRET_STRING)
            ->willReturn(self::SIGNATURE_STRING);
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertEquals($identity, $result->getIdentity());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithoutAuthHeader()
    {
        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->with('Authorization')
            ->willReturn(false);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $this->assertInstanceOf(Result::class, $adapter->authenticate());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithoutIdentifier()
    {
        $authorization = self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithoutPublicKey()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->willThrowException(new TokenException('Public kex not found', Result::FAILURE_IDENTITY_NOT_FOUND));
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithoutSignature()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
                'extractSignature',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->willReturn(self::PUBLIC_STRING);
        $adapter
            ->expects($this->once())
            ->method('extractSignature')
            ->willThrowException(new TokenException('Signature not found', Result::FAILURE_CREDENTIAL_INVALID));
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     * @uses FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException
     */
    public function testAuthenticationWithoutValidIdentity()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $identityService = $this->getMock(IdentityServiceInterface::class);
        $identityService
            ->expects($this->once())
            ->method('getIdentity')
            ->willThrowException(new IdentityNotFoundException());

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
                'extractSignature',
                'getIdentityService',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->willReturn(self::PUBLIC_STRING);
        $adapter
            ->expects($this->once())
            ->method('extractSignature')
            ->willReturn(self::SIGNATURE_STRING);
        $adapter
            ->expects($this->once())
            ->method('getIdentityService')
            ->willReturn($identityService);
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithMissMatchingSignature()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, false);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $identity = $this->getMock(IdentityInterface::class);
        $identity
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn(self::SECRET_STRING);

        $identityService = $this->getMock(IdentityServiceInterface::class);
        $identityService
            ->expects($this->once())
            ->method('getIdentity')
            ->with(self::PUBLIC_STRING)
            ->willReturn($identity);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
                'extractSignature',
                'getIdentityService',
                'getHmac',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->with($authorization)
            ->willReturn(self::PUBLIC_STRING);
        $adapter
            ->expects($this->once())
            ->method('extractSignature')
            ->with($authorization)
            ->willReturn(self::SIGNATURE_STRING);
        $adapter
            ->expects($this->once())
            ->method('getIdentityService')
            ->willReturn($identityService);
        $adapter
            ->expects($this->once())
            ->method('getHmac')
            ->with($request, self::SECRET_STRING)
            ->willReturn('invalid-signature');
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     */
    public function testAuthenticationWithMissMatchingSignatureInDebugMode()
    {
        $authorization = 'Token ' . self::PUBLIC_STRING . ':' . self::SIGNATURE_STRING;

        $header = $this->getMock(HeaderInterface::class);
        $header
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($authorization);

        $headers = $this->getMock(Headers::class);
        $headers
            ->expects($this->any())
            ->method('has')
            ->withConsecutive(['Authorization'], ['XDEBUG_SESSION_START'])
            ->willReturnOnConsecutiveCalls(true, true);
        $headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($header);

        $identity = $this->getMock(IdentityInterface::class);
        $identity
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn(self::SECRET_STRING);

        $identityService = $this->getMock(IdentityServiceInterface::class);
        $identityService
            ->expects($this->once())
            ->method('getIdentity')
            ->with(self::PUBLIC_STRING)
            ->willReturn($identity);

        $request = $this->getMock(
            Request::class,
            [
                'getHeaders',
            ],
            [],
            '',
            false
        );
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn($headers);
        /** @var Request $request */

        $adapter = $this->getMock(
            TokenHeaderAuthenticationAdapter::class,
            [
                'getRequest',
                'extractPublicKey',
                'extractSignature',
                'getIdentityService',
                'getHmac',
            ]
        );
        $adapter
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $adapter
            ->expects($this->once())
            ->method('extractPublicKey')
            ->with($authorization)
            ->willReturn(self::PUBLIC_STRING);
        $adapter
            ->expects($this->once())
            ->method('extractSignature')
            ->with($authorization)
            ->willReturn(self::SIGNATURE_STRING);
        $adapter
            ->expects($this->once())
            ->method('getIdentityService')
            ->willReturn($identityService);
        $adapter
            ->expects($this->once())
            ->method('getHmac')
            ->with($request, self::SECRET_STRING)
            ->willReturn('invalid-signature');
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }
}
