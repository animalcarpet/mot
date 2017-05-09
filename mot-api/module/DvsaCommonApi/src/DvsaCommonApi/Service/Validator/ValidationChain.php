<?php

namespace DvsaCommonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\ServiceException;

/**
 * Allows to validate multiple validators at once.
 *
 * Class ValidationChain
 */
class ValidationChain implements ValidatorInterface
{
    /** @var ValidatorInterface[] */
    private $validators = [];

    public function addValidator(ValidatorInterface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Will gather validation errors from all validators and throw them in a single BadRequestException.
     *
     * @param array $data
     */
    public function validate(array $data)
    {
        $errors = new ErrorSchema();

        foreach ($this->validators as $validator) {
            try {
                $validator->validate($data);
            } catch (ServiceException $exception) {
                $errors->addException($exception);
            }
        }

        $errors->throwIfAny();
    }
}
