<?php
/**
 * Abstarct header authentication adapter file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;

/**
 * Class AbstractHeaderAuthenticationAdapter
 *
 * @package FinalGene\RestResourceAuthenticationModule\Authentication\Adapter
 */
abstract class AbstractHeaderAuthenticationAdapter implements AdapterInterface
{
    private $request;

    /**
     * Get $request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return AbstractHeaderAuthenticationAdapter
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Authenticate
     *
     * @return Result
     */
    public function authenticate()
    {
        return $this->buildErrorResult('No authentication implemented', Result::FAILURE_UNCATEGORIZED);
    }

    /**
     * Build error result object
     *
     * @param $message
     * @param int $code
     *
     * @return Result
     */
    protected function buildErrorResult($message, $code = Result::FAILURE)
    {
        return new Result($code, null, [$message]);
    }
}
