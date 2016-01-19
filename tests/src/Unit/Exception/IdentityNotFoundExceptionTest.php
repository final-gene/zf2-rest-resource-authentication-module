<?php
/**
 * rest-resource-authentication-module
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Exception;

use FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException;

/**
 * Class IdentityNotFoundExceptionTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Exception
 */
class IdentityNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException::__construct
     * @uses \Exception
     */
    public function testConstructor()
    {
        $exception = new IdentityNotFoundException();
        $this->assertEquals('Identity not found', $exception->getMessage());
    }
}
