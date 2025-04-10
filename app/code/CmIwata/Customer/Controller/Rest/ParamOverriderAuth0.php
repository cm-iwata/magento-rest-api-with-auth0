<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CmIwata\Customer\Controller\Rest;

use CmIwata\Customer\Api\Auth0JwtUserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

class ParamOverriderAuth0 implements ParamOverriderInterface
{

    private Auth0JwtUserContextInterface $aws0JwtUserContext;

    public function __construct(Auth0JwtUserContextInterface $aws0JwtUserContext)
    {
        $this->aws0JwtUserContext = $aws0JwtUserContext;
    }

    public function getOverriddenValue():int
    {
        return $this->aws0JwtUserContext->getMagentoCustomerId();
    }
}
