<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CmIwata\Customer\Model;

use CmIwata\Customer\Api\Auth0JwtUserContextInterface;
use Magento\Framework\AuthorizationInterface;

class Auth0Authorization implements AuthorizationInterface
{

    private Auth0JwtUserContextInterface $userContext;

    public function __construct(
        Auth0JwtUserContextInterface $userContext,
    ) {
        $this->userContext = $userContext;
    }

    public function isAllowed($resource, $privilege = null): bool
    {
        if ($resource === 'auth0_self'
            && $this->userContext->getMagentoCustomerId()
        ) {
            return true;
        }

        return false;
    }
}
