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
use Prophecy\Argument;
use Zend\Authentication\Result;
use Zend\Http\Header\ContentType;
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::setIdentityService
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::getIdentityService
     */
    public function testSetAndGetIdentityService()
    {
        $expectedIdentityService = $this->getMock(IdentityServiceInterface::class);
        /** @var IdentityServiceInterface $expectedIdentityService */

        $adapter = new TokenHeaderAuthenticationAdapter();

        $adapter->setIdentityService($expectedIdentityService);
        $this->assertEquals($expectedIdentityService, $adapter->getIdentityService());
    }

    public function dataProviderForTestGetHmac()
    {
        return [
            'Method GET' => [
                Request::METHOD_GET,
                $this->never(),
            ],
            'Method POST' => [
                Request::METHOD_POST,
                $this->once(),
            ],
            'Method PUT' => [
                Request::METHOD_PUT,
                $this->never(),
            ],
        ];
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::getHmac
     * @dataProvider dataProviderForTestGetHmac
     */
    public function testGetHmac($method, $expectedCallOfPreparePostCopy)
    {
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
                'getMethod',
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
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        /** @var Request $request */

        $adapter = $this->getMock(TokenHeaderAuthenticationAdapter::class, [
            'preparePostCopy',
        ]);
        $adapter
            ->expects($expectedCallOfPreparePostCopy)
            ->method('preparePostCopy');

        $getHmac = $this->getMethod('getHmac');
        $hmac = $getHmac->invokeArgs($adapter, [$request, self::SECRET_STRING]);

        $this->assertEquals(self::SIGNATURE_STRING, $hmac);
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractSignature
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractSignature
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractPublicKey
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::extractPublicKey
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
     * @uses \FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException
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
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::isDebugLogging
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->expects($this->once())
            ->method('has')
            ->with('Authorization')
            ->willReturn(true);
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
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::authenticate
     * @uses \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\AbstractHeaderAuthenticationAdapter::buildErrorResult
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
            ->with('Authorization')
            ->willReturn(true);
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
                'isDebugLogging',
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
        $adapter
            ->expects($this->once())
            ->method('isDebugLogging')
            ->willReturn(true);
        /** @var TokenHeaderAuthenticationAdapter $adapter */

        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $adapter->authenticate();

        PHPUnit_Framework_Error_Notice::$enabled = false;
        $result = $adapter->authenticate();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::setDebugLogging
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::isDebugLogging
     */
    public function testSetAndGetDebugLogging()
    {
        $adapter = new TokenHeaderAuthenticationAdapter();
        $this->assertFalse($adapter->isDebugLogging());

        $adapter->setDebugLogging(true);
        $this->assertTrue($adapter->isDebugLogging());
    }

    public function dataProviderForTestPreparePostCopy()
    {
        return [
            'unknown content type' => [
                null,
                'shouldNotBeCalled',
            ],
            'multipart/form-data with no data' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4--\r\n",
            ],
            'multipart/form-data with POST data (name and value)' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 3\r\n\r\nbar\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => 'bar',
                ],
            ],
            'multipart/form-data with POST data (name only)' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\n\r\n\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => '',
                ],
            ],
            'multipart/form-data with POST data with zero int value' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 1\r\n\r\n0\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => 0,
                ],
            ],
            'multipart/form-data with POST data with zero float value' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 3\r\n\r\n0.0\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => 0.0,
                ],
            ],
            'multipart/form-data with POST data with boolean false value' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 5\r\n\r\nfalse\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => false,
                ],
            ],
            'multipart/form-data with FILE data' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"file\"; filename=\"foo.txt\"\r\nContent-Length: 5\r\nContent-Type: text/plain\r\n\r\n1234\n\r\n--58971ed4dfcc4--\r\n",
                [],
                [
                    'file' => [
                        'name' => 'foo.txt',
                        'size' => 5,
                        'type' => 'text/plain',
                        'tmp_name' => __DIR__ . '/../../../../resources/Unit/Authentication/Adapter/TokenHeaderAuthenticationAdapterTest/testPreparePostCopy/test.txt',
                    ],
                ],
            ],
            'multipart/form-data with POST and FILE data' => [
                ContentType::fromString('Content-Type: multipart/form-data; boundary=58971ed4dfcc4'),
                'shouldBeCalled',
                "--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 3\r\n\r\nbar\r\n--58971ed4dfcc4\r\nContent-Disposition: form-data; name=\"file\"; filename=\"foo.txt\"\r\nContent-Length: 5\r\nContent-Type: text/plain\r\n\r\n1234\n\r\n--58971ed4dfcc4--\r\n",
                [
                    'foo' => 'bar',
                ],
                [
                    'file' => [
                        'name' => 'foo.txt',
                        'size' => 5,
                        'type' => 'text/plain',
                        'tmp_name' => __DIR__ . '/../../../../resources/Unit/Authentication/Adapter/TokenHeaderAuthenticationAdapterTest/testPreparePostCopy/test.txt',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Authentication\Adapter\TokenHeaderAuthenticationAdapter::preparePostCopy
     * @dataProvider dataProviderForTestPreparePostCopy
     */
    public function testPreparePostCopy($contentType, $callExpectation, $expectedContent = '', $postData = [], $fileData = [])
    {
        $headers = $this->prophesize(Headers::class);
        $headers->get('Content-Type')
            ->shouldBeCalled()
            ->willReturn($contentType);
        $headers = $headers->reveal();

        $request = $this->prophesize(Request::class);
        $request->getHeaders()
            ->shouldBeCalled()
            ->willReturn($headers);
        $request->getPost()
            ->$callExpectation()
            ->willReturn($postData);
        $request->getFiles()
            ->$callExpectation()
            ->willReturn($fileData);
        $request = $request->reveal();

        $requestCopy = $this->prophesize(Request::class);
        $requestCopy->setContent($expectedContent)
            ->$callExpectation();
        $requestCopy = $requestCopy->reveal();

        $preparePostCopy = $this->getMethod('preparePostCopy');
        $adapter = new TokenHeaderAuthenticationAdapter();
        $preparePostCopy->invokeArgs($adapter, [$request, $requestCopy]);
    }
}
