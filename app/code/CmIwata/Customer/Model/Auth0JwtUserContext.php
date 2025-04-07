<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CmIwata\Customer\Model;

use CmIwata\Customer\Api\Auth0JwtUserContextInterface;
use CmIwata\Customer\Api\Auth0JwtUserTokenReaderInterface;
use CmIwata\Customer\Api\Auth0JwtUserTokenValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Integration\Api\Exception\UserTokenException;

class Auth0JwtUserContext implements ResetAfterRequestInterface, Auth0JwtUserContextInterface
{

    protected Http $request;

    protected bool $isRequestProcessed = false;

    private readonly Auth0JwtUserTokenReaderInterface $userTokenReader;

    private readonly Auth0JwtUserTokenValidatorInterface $userTokenValidator;

    protected readonly ClaimsPayloadInterface $claims;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        ?Auth0JwtUserTokenReaderInterface $tokenReader = null,
        ?Auth0JwtUserTokenValidatorInterface $tokenValidator = null
    ) {
        $this->request = $request;
        $this->userTokenReader = $tokenReader ?? ObjectManager::getInstance()->get(Auth0JwtUserTokenReaderInterface::class);
        $this->userTokenValidator = $tokenValidator
            ?? ObjectManager::getInstance()->get(Auth0JwtUserTokenValidatorInterface::class);
    }

    public function getMagentoCustomerId():?int
    {
        $this->processRequest();
        if (!isset($this->claims)) {
            return null;
        }
        return $this->claims->getClaims()['http://localhost/user_metadata']->getValue()['magento_customer_id'];
    }

    public function _resetState(): void
    {
        $this->isRequestProcessed = null;
        $this->claims = null;
    }

    protected function processRequest(): void
    {
        if ($this->isRequestProcessed) {
            return;
        }

        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return;
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== 'bearer') {
            $this->isRequestProcessed = true;
            return;
        }

        $bearerToken = $headerPieces[1];
        try {
            $token = $this->userTokenReader->read($bearerToken);
        } catch (UserTokenException $exception) {
            $this->isRequestProcessed = true;
            return;
        }
        try {
            $this->userTokenValidator->validate($token);
        } catch (AuthorizationException $exception) {
            $this->isRequestProcessed = true;
            return;
        }
        $this->claims = $token->getJwtClaims();
        $this->isRequestProcessed = true;
    }
}
