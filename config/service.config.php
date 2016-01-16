<?php
/**
 * Service manager config file
 *
 * @copyright Copyright (c) 2015, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule;

use FinalGene\RestResourceAuthenticationModule\ServiceManager\AuthenticationServiceInitializer;
use FinalGene\RestResourceAuthenticationModule\Http\Request;
use FinalGene\RestResourceAuthenticationModule\Http\RequestFactory;

return [
    'service_manager' => [
        'initializers' => [
            'AuthenticationServiceInitializer' => AuthenticationServiceInitializer::class,
        ],
        'invokables' => [
        ],
        'factories' => [
            Request::class => RequestFactory::class,
        ],
    ],
];
