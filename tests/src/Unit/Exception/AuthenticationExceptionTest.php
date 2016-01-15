<?php
/**
 * Authentication service file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Exception;

use FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException;

/**
 * Class AuthenticationExceptionTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Exception
 */
class AuthenticationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException::setAuthenticationMessages
     * @covers FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException::getAuthenticationMessages
     * @uses FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException::__construct
     */
    public function testSetAndGetAuthenticationMessages()
    {
        $exception = new AuthenticationException();

        $expected = [
            'foo'
        ];

        $exception->setAuthenticationMessages($expected);
        $this->assertEquals($expected, $exception->getAuthenticationMessages());
    }
}
