<?php
/**
 * Abstarct header authentication adapter file
 *
 * @copyright Copyright (c) 2016, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace FinalGene\RestResourceAuthenticationModule\Authentication\Adapter;

use FinalGene\RestResourceAuthenticationModule\Exception\TokenException;
use FinalGene\RestResourceAuthenticationModule\Exception\IdentityNotFoundException;
use FinalGene\RestResourceAuthenticationModule\Service\IdentityServiceInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;

/**
 * Class TokenHeaderAuthenticationAdapter
 *
 * @package FinalGene\RestResourceAuthenticationModule\Authentication\Adapter
 */
class TokenHeaderAuthenticationAdapter extends AbstractHeaderAuthenticationAdapter
{
    const AUTH_HEADER = 'Authorization';
    const AUTH_IDENTIFIER = 'Token';

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var bool
     */
    private $debugLogging = false;

    /**
     * Get $identityService
     *
     * @return IdentityServiceInterface
     */
    public function getIdentityService()
    {
        return $this->identityService;
    }

    /**
     * @param IdentityServiceInterface $identityService
     * @return TokenHeaderAuthenticationAdapter
     */
    public function setIdentityService(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authenticate()
    {
        $request = $this->getRequest();
        $header = $request->getHeaders();

        if (!$header->has(self::AUTH_HEADER)) {
            return $this->buildErrorResult('Authorization header missing');
        }

        $authorization = $header->get(self::AUTH_HEADER)->getFieldValue();
        if (0 !== strpos($authorization, self::AUTH_IDENTIFIER . ' ')) {
            return $this->buildErrorResult('Invalid authorization header');
        }

        try {
            $publicKey = $this->extractPublicKey($authorization);
            $signature = $this->extractSignature($authorization);
            $identity = $this->getIdentityService()->getIdentity($publicKey);

        } catch (TokenException $e) {
            return $this->buildErrorResult($e->getMessage(), $e->getCode());

        } catch (IdentityNotFoundException $e) {
            return $this->buildErrorResult($e->getMessage(), Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        $hmac = $this->getHmac($request, $identity->getSecret());
        if ($hmac !== $signature) {
            if ($this->isDebugLogging()) {
                trigger_error(sprintf('Signature for identity `%s`: %s', $publicKey, $hmac), E_USER_NOTICE);
            }
            return $this->buildErrorResult('Signature does not match', Result::FAILURE_CREDENTIAL_INVALID);
        }

        return new Result(Result::SUCCESS, $identity);
    }

    /**
     * Extract public key from authorization
     *
     * @param $authorization
     *
     * @return string
     * @throws TokenException
     */
    protected function extractPublicKey($authorization)
    {
        $identifierLength = strlen(self::AUTH_IDENTIFIER) + 1;
        $publicKey = substr(
            $authorization,
            $identifierLength,
            strpos($authorization, ':') - $identifierLength
        );
        if (empty($publicKey)) {
            throw new TokenException(
                'Public kex not found',
                Result::FAILURE_IDENTITY_NOT_FOUND
            );
        }

        return $publicKey;
    }

    /**
     * Extract signature from authorization
     *
     * @param $authorization
     *
     * @return string
     * @throws TokenException
     */
    protected function extractSignature($authorization)
    {
        $signatureStart = strpos($authorization, ':');
        if (false === $signatureStart) {
            throw new TokenException(
                'Signature not found',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }

        return substr($authorization, $signatureStart + 1);
    }

    /**
     * Calculate HMAC for request
     *
     * @param Request $request
     * @param string $secret
     *
     * @return string
     */
    protected function getHmac(Request $request, $secret)
    {
        // Remove headers to build valid signature
        $headerCopy = clone $request->getHeaders();
        $headerCopy->clearHeaders();

        $requestCopy = clone $request;
        $requestCopy->setHeaders($headerCopy);

        return hash_hmac('sha256', $requestCopy->toString(), $secret);
    }

    /**
     * Is $debugLog
     *
     * @return boolean
     */
    public function isDebugLogging()
    {
        return $this->debugLogging;
    }

    /**
     * @param boolean $debugLogging
     * @return TokenHeaderAuthenticationAdapter
     */
    public function setDebugLogging($debugLogging)
    {
        $this->debugLogging = filter_var($debugLogging, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }
}
