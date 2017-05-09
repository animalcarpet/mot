<?php

namespace DvsaAuthentication\Identity;

/**
 * Generic resolver for identities based on token.
 */
interface IdentityByTokenResolver
{
    public function resolve($token);
}
