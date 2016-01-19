<?php
/**
 * Authentication exception file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Exception;

/**
 * Class AuthenticationException
 *
 * @package FinalGene\RestResourceAuthenticationModule\Exception
 */
class AuthenticationException extends \Exception
{
    /**
     * @var array
     */
    protected $authenticationMessages = [];

    /**
     * Set $authenticationMessages
     *
     * @param array $authenticationMessages
     *
     * @return $this
     */
    public function setAuthenticationMessages(array $authenticationMessages)
    {
        $this->authenticationMessages = $authenticationMessages;
        return $this;
    }

    /**
     * Get $authenticationMessages
     *
     * @return array
     */
    public function getAuthenticationMessages()
    {
        return $this->authenticationMessages;
    }
}
