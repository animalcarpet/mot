<?php

namespace PersonApi\Service\Validator\MotTestingCertificate;

use DvsaEntities\Repository\SiteRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use Zend\Validator\AbstractValidator;

class SiteNumberValidator extends AbstractValidator
{
    const MSG_NOT_FOUND = 'msgNotFound';
    const ERROR_NOT_EXISTS = "site with '%value%' id does not exist";

    private $siteRepository;

    protected $messageTemplates = [self::MSG_NOT_FOUND => self::ERROR_NOT_EXISTS];

    public function __construct(SiteRepository $siteRepository, $options = null)
    {
        parent::__construct($options);

        $this->siteRepository = $siteRepository;
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if (empty($value)) {
            return true;
        }

        try {
            $this->siteRepository->getBySiteNumber($value);
        } catch (NotFoundException $e) {
            $this->error(self::MSG_NOT_FOUND);

            return false;
        }

        return true;
    }
}
