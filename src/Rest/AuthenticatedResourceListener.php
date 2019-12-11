<?php
/**
 * Authenticated resource listener file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Rest;

use FinalGene\RestResourceAuthenticationModule\Authentication\IdentityInterface;
use FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException;
use FinalGene\RestResourceAuthenticationModule\Exception\PermissionException;
use Zend\EventManager\EventManagerInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\ResourceEvent;
use FinalGene\RestResourceAuthenticationModule\Service\AuthenticationService;

/**
 * Class AuthenticatedResourceListener
 *
 * @package FinalGene\RestResourceAuthenticationModule\Rest
 */
abstract class AuthenticatedResourceListener extends AbstractResourceListener
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * Set $authenticationService
     *
     * @param AuthenticationService $authenticationService
     *
     * @return $this
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        return $this;
    }

    /**
     * Get $authenticationService
     *
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->attach('create', [$this, 'authenticate'], 10);
        $events->attach('delete', [$this, 'authenticate'], 10);
        $events->attach('deleteList', [$this, 'authenticate'], 10);
        $events->attach('fetch', [$this, 'authenticate'], 10);
        $events->attach('fetchAll', [$this, 'authenticate'], 10);
        $events->attach('patch', [$this, 'authenticate'], 10);
        $events->attach('patchList', [$this, 'authenticate'], 10);
        $events->attach('replaceList', [$this, 'authenticate'], 10);
        $events->attach('update', [$this, 'authenticate'], 10);

        parent::attach($events);
    }

    /**
     * @param ResourceEvent $event
     *
     * @return null|object|ApiProblemResponse
     */
    public function authenticate(ResourceEvent $event)
    {
        try {
            $identity = $this->getAuthenticationService()->authenticate();
            if ($identity instanceof IdentityInterface) {
                $identity->checkPermission($event);
            }
            return $identity;

        } catch (AuthenticationException $e) {
            return new ApiProblemResponse(
                new ApiProblem(
                    400,
                    sprintf('%s (%s)', $e->getMessage(), implode(', ', $e->getAuthenticationMessages()))
                )
            );

        } catch (PermissionException $e) {
            return new ApiProblemResponse(new ApiProblem(400, $e->getMessage()));
        }
    }
}
