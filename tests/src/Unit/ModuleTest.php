<?php
/**
 * This file is part of the rest-resource-authentication-module project.
 *
 * @copyright Copyright (c) 2015, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModuleTest\Unit;

use FinalGene\RestResourceAuthenticationModule\Module;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Class ModuleTest
 *
 * @package FinalGene\RestResourceAuthenticationModuleTest\Unit
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Make sure module config can be serialized.
     *
     * Make sure module config can be serialized, because if not,
     * this breaks the application when zf2's config cache is enabled.
     *
     * @covers \FinalGene\RestResourceAuthenticationModule\Module::getConfig()
     * @uses \FinalGene\RestResourceAuthenticationModule\Module::loadConfig()
     */
    public function testModuleConfigIsSerializable()
    {
        $module = new Module();

        if (!$module instanceof ConfigProviderInterface) {
            $this->markTestSkipped('Module does not provide config');
        }

        $this->assertEquals($module->getConfig(), unserialize(serialize($module->getConfig())));
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Module::getModuleDependencies()
     */
    public function testModuleDependencies()
    {
        $module = new Module();
        $dependencies = $module->getModuleDependencies();

        $this->assertInternalType('array', $dependencies);

        $this->assertContains('ZF\ApiProblem', $dependencies);
        $this->assertContains('ZF\Rest', $dependencies);
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Module::loadConfig()
     * @expectedException \InvalidArgumentException
     */
    public function testLoadConfigThrowException()
    {
        $module = new Module();

        $config = $this->getMethod('loadConfig');
        $config->invokeArgs($module, ['not.existing.file']);
    }

    /**
     * @covers \FinalGene\RestResourceAuthenticationModule\Module::loadConfig()
     */
    public function testLoadConfigReturnConfigArray()
    {
        $module = new Module();

        $config = $this->getMethod('loadConfig');
        $config = $config->invokeArgs($module, ['tests/resources/Unit/ModuleTest/service.config.php']);

        $this->assertInternalType('array', $config);
    }

    /**
     * @param $name
     *
     * @return \ReflectionMethod
     */
    protected function getMethod($name)
    {
        $class = new \ReflectionClass(Module::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
