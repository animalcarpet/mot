<?php

namespace PersonApi\Input\MotTestingCertificate;

use DvsaEntities\Repository\SiteRepository;
use Zend\InputFilter\Input;
use PersonApi\Service\Validator\MotTestingCertificate\SiteNumberValidator;

class SiteNumberInput extends Input
{
    const FIELD = 'siteNumber';

    public function __construct(SiteRepository $siteRepository)
    {
        parent::__construct(self::FIELD);

        $siteNumberValidator = new SiteNumberValidator($siteRepository);

        $this
            ->setRequired(false)
            ->getValidatorChain()
            ->attach($siteNumberValidator);
    }
}
