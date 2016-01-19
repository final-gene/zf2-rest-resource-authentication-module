<?php
/**
 * Identity interface file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Authentication;

use FinalGene\RestResourceAuthenticationModule\Exception\PermissionException;
use ZF\Rest\ResourceEvent;

/**
 * Class IdentityInterface
 *
 * @package FinalGene\RestResourceAuthenticationModule\Authentication
 */
interface IdentityInterface
{
    /**
     * Get secret from identity
     *
     * @return string
     */
    public function getSecret();

    /**
     * Check permissions
     *
     * @param ResourceEvent $event
     *
     * @throws PermissionException
     */
    public function checkPermission(ResourceEvent $event);
}
