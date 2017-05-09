<?php

namespace UserApi\HelpDesk\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Model\SearchPersonModel;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class SearchPersonValidator.
 */
class SearchPersonValidator extends AbstractValidator
{
    const ERROR_INCORRECT_DATE = 'Invalid date of birth';
    const ERROR_DATE_IN_FUTURE = 'Date in future';

    /**
     * At least one fields must not be null (empty).
     *
     * @param SearchPersonModel $model
     *
     * @throws BadRequestException
     */
    public function validate(SearchPersonModel $model)
    {
        $dateOfBirth = $model->getDateOfBirth();
        $dateOfBirthNullOrEmpty = $this->isNullOrEmptyString($dateOfBirth);

        if ($this->isNullOrEmptyString($model->getUsername())
            && $this->isNullOrEmptyString($model->getFirstName())
            && $this->isNullOrEmptyString($model->getLastName())
            && $this->isNullOrEmptyString($model->getEmail())
            && $this->isNullOrEmptyString($model->getDateOfBirth())
            && $this->isNullOrEmptyString($model->getPostcode())
            && $this->isNullOrEmptyString($model->getTown())
        ) {
            $this->errors->add('At least one field must be not empty');
        }

        if (!$dateOfBirthNullOrEmpty) {
            $this->validateDateOfBirth($dateOfBirth);
        }

        $this->errors->throwIfAny();
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isNullOrEmptyString($value)
    {
        return is_null($value) || (strlen(strval($value)) === 0);
    }

    private function validateDateOfBirth($dateOfBirth)
    {
        try {
            if (DateUtils::isDateInFuture(DateUtils::toDate($dateOfBirth))) {
                $this->errors->add(self::ERROR_DATE_IN_FUTURE, 'dateOfBirth');
            }
        } catch (\Exception $e) {
            $this->errors->add(self::ERROR_INCORRECT_DATE, 'dateOfBirth');
        }
    }
}
