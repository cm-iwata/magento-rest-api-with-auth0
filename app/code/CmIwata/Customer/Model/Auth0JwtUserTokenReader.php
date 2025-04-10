<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace CmIwata\Customer\Model;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Token as Auth0Token;
use CmIwata\Customer\Api\Auth0JwtUserTokenReaderInterface;
use Magento\Framework\Jwt\Claim\Audience;
use Magento\Framework\Jwt\Claim\IssuedAt;
use Magento\Framework\Jwt\Claim\Issuer;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Claim\Subject;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\JwtUserToken\Model\Data\Header;
use Magento\JwtUserToken\Model\Data\JwtTokenData;

class Auth0JwtUserTokenReader implements Auth0JwtUserTokenReaderInterface
{

    private SdkConfiguration $sdkConfiguration;

    public function __construct(SdkConfiguration $sdkConfiguration)
    {
        $this->sdkConfiguration = $sdkConfiguration;
    }

    public function read(string $token): JwtTokenData
    {

        try {
            $auth0Token = new Auth0Token($this->sdkConfiguration, $token, Auth0Token::TYPE_ID_TOKEN);
            $auth0Token->verify();
        } catch (InvalidTokenException $exception) {
            throw new UserTokenException('Failed to read JWT token', $exception);
        }

        $arrToken = $auth0Token->toArray();
        $iat = \DateTimeImmutable::createFromFormat('U', (string) $auth0Token->getIssued());
        $exp = \DateTimeImmutable::createFromFormat('U', (string) $auth0Token->getExpiration());
        $namespace = 'http://localhost/user_metadata';
        $uid = $arrToken[$namespace]['magento_customer_id'];
        $claim = new ClaimsPayload(
            [
                new Audience($auth0Token->getAudience()),
                new Subject($auth0Token->getSubject()),
                new PrivateClaim(
                    $namespace,
                    $arrToken[$namespace]
                ),
                new IssuedAt($iat),
                new Issuer($auth0Token->getIssuer())
            ]
        );

        // TODO 今回Headerは利用しないので空配列で返却している
        return new JwtTokenData($iat, $exp, new Header([]), $claim);
    }
}
