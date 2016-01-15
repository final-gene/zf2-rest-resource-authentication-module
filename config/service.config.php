<?php
/**
 * Service manager config file
 *
 * @copyright Copyright (c) 2015, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule;

use FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService;
use FinalGene\RestResourceAuthenticationModule\ServiceManager\AuthenticationServiceInitializer;

return [
    'service_manager' => [
        'initializers' => [
            'AuthenticationServiceInitializer' => AuthenticationServiceInitializer::class,
        ],
        'invokables' => [
            AuthenticationService::class => AuthenticationService::class,
        ],
        'factories' => [
        ],
    ],
];
