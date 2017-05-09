<?php

namespace DvsaCommonApi\Service\Validator;

/**
 * Apparently AbstractValidator doesn't have validate method.
 *
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    public function validate(array $data);
}
