<?php
/**
 * rest-resource-authentication-module
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit\Http;

use FinalGene\RestResourceAuthenticationModule\Http\Request;
use Zend\Test\Util\ModuleLoader;
use Zend\Console\Console;
use Zend\Console\Request as ConsoleRequest;

/**
 * Class RequestFactoryTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit\Http
 */
class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        /* @noinspection PhpIncludeInspection */
        $moduleLoader = new ModuleLoader([
            'modules' => [
                'ZF\ApiProblem',
                'ZF\Rest',
                'FinalGene\RestResourceAuthenticationModule',
            ],
            'module_listener_options' => [
            ],
        ]);
        $this->serviceManager = $moduleLoader->getServiceManager();
    }

    /**
     * Get the service manager
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @covers FinalGene\RestResourceAuthenticationModule\Http\RequestFactory::createService
     * @uses FinalGene\RestResourceAuthenticationModule\Http\Request
     * @uses FinalGene\RestResourceAuthenticationModule\Module
     * @uses FinalGene\RestResourceAuthenticationModule\ServiceManager\AuthenticationServiceInitializer
     */
    public function testCreateService()
    {
        $this->assertInstanceOf(
            ConsoleRequest::class,
            $this->getServiceManager()->get(Request::class)
        );
    }
}
