<?php
/**
 * Identity service interface file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Service;

use FinalGene\RestResourceAuthenticationModule\Authentication\IdentityInterface;

/**
 * Class IdentityServiceInterface
 *
 * @package FinalGene\RestResourceAuthenticationModule\Service
 */
interface IdentityServiceInterface
{
    /**
     * Get identity
     *
     * @param string $public Public information to find identity
     *
     * @return IdentityInterface
     */
    public function getIdentity($public);
}
