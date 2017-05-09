<?php

namespace Organisation\ViewModel\MotTestLog;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaClient\ViewModel\DateTimeViewModel;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Messages\DateErrors;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Stdlib\Parameters;

class MotTestLogFormViewModel extends AbstractFormModel
{
    const FLD_DATE_FROM = 'dateFrom';
    const FLD_DATE_TO = 'dateTo';

    const VALIDATION_MAX_DAYS = 31;

    /**
     * @var DateTimeViewModel
     */
    private $dateFrom;
    /**
     * @var DateTimeViewModel
     */
    private $dateTo;

    public function __construct()
    {
        $this->setDateFrom(new DateTimeViewModel());
        $this->setDateTo(new DateTimeViewModel());
    }

    public function parseData(Parameters $formData)
    {
        $dateFrom = $formData->get(self::FLD_DATE_FROM);
        $dateTo = $formData->get(self::FLD_DATE_TO);

        if (is_array($dateFrom)) {
            if (!empty($dateFrom)) {
                $this->setDateFrom(
                    new DateTimeViewModel(
                        ArrayUtils::tryGet($dateFrom, 'Year'),
                        ArrayUtils::tryGet($dateFrom, 'Month'),
                        ArrayUtils::tryGet($dateFrom, 'Day')
                    )
                );
            }

            if (!empty($dateTo)) {
                $this->setDateTo(
                    new DateTimeViewModel(
                        ArrayUtils::tryGet($dateTo, 'Year'),
                        ArrayUtils::tryGet($dateTo, 'Month'),
                        ArrayUtils::tryGet($dateTo, 'Day'),
                        23, 59, 59
                    )
                );
            }
        } else {
            $date = $formData->get(SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM);
            if (!empty($date)) {
                $this->setDateFrom((new DateTimeViewModel())->setDate(new \DateTime('@'.$date)));
            }

            $date = $formData->get(SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM);
            if (!empty($date)) {
                $this->setDateTo((new DateTimeViewModel())->setDate(new \DateTime('@'.$date)));
            }
        }

        return $this;
    }

    /**
     * @return DateTimeViewModel
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param DateTimeViewModel $dateFrom
     *
     * @return $this
     */
    public function setDateFrom(DateTimeViewModel $dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * @return DateTimeViewModel
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param DateTimeViewModel $dateTo
     *
     * @return $this
     */
    public function setDateTo(DateTimeViewModel $dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function isValid()
    {
        $this->validateDate($this->getDateFrom(), self::FLD_DATE_FROM);
        $this->validateDate($this->getDateTo(), self::FLD_DATE_TO);

        $dateFrom = $this->getDateFrom()->getDate();
        $dateTo = $this->getDateTo()->getDate();

        if ($dateFrom && $dateTo) {
            if ($dateFrom > $dateTo) {
                $this->addError(self::FLD_DATE_FROM, DateErrors::AFTER_TO);
            }

            if ($dateFrom->diff($dateTo)->days > self::VALIDATION_MAX_DAYS) {
                $this->addError(self::FLD_DATE_FROM, DateErrors::RANGE_31D);
            }
        }

        return !$this->hasErrors();
    }

    private function validateDate(DateTimeViewModel $model, $field)
    {
        $date = $model->getDate();

        if (trim($model->getYear()) === '' || trim($model->getMonth()) === '' || trim($model->getDay()) === '') {
            $this->addError($field, DateErrors::INVALID_FORMAT);
        } elseif (
            $date === null
            || $date < new \DateTime('1900-01-01')
        ) {
            $this->addError($field, DateErrors::NOT_EXIST);
        } elseif (DateUtils::isDateInFuture($date)) {
            $this->addError($field, DateErrors::IN_FUTURE);
        }
    }
}
