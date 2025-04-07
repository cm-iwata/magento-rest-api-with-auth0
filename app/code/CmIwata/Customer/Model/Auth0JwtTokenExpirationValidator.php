<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace CmIwata\Customer\Model;

use CmIwata\Customer\Api\Auth0JwtUserTokenValidatorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Stdlib\DateTime\DateTime as DtUtil;
use Magento\JwtUserToken\Model\Data\JwtTokenData;

class Auth0JwtTokenExpirationValidator implements Auth0JwtUserTokenValidatorInterface
{

    private DtUtil $datetimeUtil;

    public function __construct(DtUtil $datetimeUtil)
    {
        $this->datetimeUtil = $datetimeUtil;
    }

    public function validate(JwtTokenData $token): void
    {
        if ($this->isTokenExpired($token)) {
            throw new AuthorizationException(__('Token has expired'));
        }
    }

    private function isTokenExpired(JwtTokenData $token): bool
    {
        return $token->getExpires()->getTimestamp() <= $this->datetimeUtil->gmtTimestamp();
    }

}
