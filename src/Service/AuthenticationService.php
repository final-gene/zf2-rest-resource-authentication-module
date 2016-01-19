<?php
/**
 * Authentication service file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Service;

use FinalGene\RestResourceAuthenticationModule\Exception\AuthenticationException;
use Zend\Authentication\Adapter\AdapterInterface;
use ZF\MvcAuth\Identity\IdentityInterface;

/**
 * Class AuthenticationService
 *
 * @package FinalGene\RestResourceAuthenticationModule\Service
 */
class AuthenticationService
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Get $adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @return AuthenticationService
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return IdentityInterface|null
     * @throws AuthenticationException
     */
    public function authenticate()
    {
        $result = $this->getAdapter()->authenticate();

        if (!$result->isValid()) {
            $authException = new AuthenticationException(
                'Could not authenticate',
                $result->getCode()
            );
            $authException->setAuthenticationMessages($result->getMessages());

            throw $authException;
        }

        return $result->getIdentity();
    }
}
