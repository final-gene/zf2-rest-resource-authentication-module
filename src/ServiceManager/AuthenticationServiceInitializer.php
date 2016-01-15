<?php
/**
 * Authenticated service initializer file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\ServiceManager;

use FinalGene\RestResourceAuthenticationModule\Rest\AuthenticatedResourceListener;
use FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthenticationServiceInitializer
 *
 * @package FinalGene\RestResourceAuthenticationModule\ServiceManager
 */
class AuthenticationServiceInitializer implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param object $instance
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof AuthenticatedResourceListener) {
            /** @var AuthenticationService $authenticationService */
            $authenticationService = $serviceLocator->get(AuthenticationService::class);
            $instance->setAuthenticationService($authenticationService);
        }
    }
}
