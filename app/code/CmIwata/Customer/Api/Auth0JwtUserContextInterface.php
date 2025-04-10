<?php
namespace CmIwata\Customer\Api;

interface Auth0JwtUserContextInterface
{
    public function getMagentoCustomerId(): ?int;
}
