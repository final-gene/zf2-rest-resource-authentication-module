<?php
/**
 * Request file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Http;

use League\Uri\QueryParser;
use Zend\Http\PhpEnvironment\Request as BaseRequest;
use Zend\Stdlib\Parameters;

/**
 * Class Request
 *
 * @package FinalGene\RestResourceAuthenticationModule\Http
 */
class Request extends BaseRequest
{
    public function __construct($allowCustomMethods = true)
    {
        parent::__construct($allowCustomMethods);

        $parser = new QueryParser();
        $queryParameters = $parser->parse($this->getUri()->getQuery());

        $this->setQuery(new Parameters($queryParameters));
    }
}
