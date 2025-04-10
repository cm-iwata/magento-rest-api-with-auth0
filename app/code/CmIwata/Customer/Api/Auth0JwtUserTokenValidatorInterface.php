<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace CmIwata\Customer\Api;

use Magento\JwtUserToken\Model\Data\JwtTokenData;

interface Auth0JwtUserTokenValidatorInterface
{
    public function validate(JwtTokenData $token): void;
}
