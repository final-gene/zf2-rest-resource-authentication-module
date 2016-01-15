# REST resource authentication module

## Usage
Bind authentication functionality to REST resources

## Installation

+ `$ composer require final-gene/rest-resource-authentication-module`
+ add `FinalGene\RestResourceAuthenticationModule` to the modules array in your application config
+ register your factory/invokable class in the service manager config

        [
         'service_manager' => [
             'invokables' => [
                 'FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService' => 'FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService',
             ],
             'factories' => [
             ],
         ],
        ];
 
