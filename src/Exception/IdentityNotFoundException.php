<?php
/**
 * Identity not found exception file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Exception;
use Exception;

/**
 * Class IdentityNotFoundException
 *
 * @package FinalGene\RestResourceAuthenticationModule\Exception
 */
class IdentityNotFoundException extends \Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'Identity not found';
        }
        parent::__construct($message, $code, $previous);
    }

}
