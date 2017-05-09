<?php

namespace PersonApi\Service\Validator\MotTestingCertificate;

use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use Zend\Validator\AbstractValidator;

class MotTestingCertificateNotExistValidator extends AbstractValidator
{
    const MSG_NOT_FOUND = 'msgFound';
    const ERROR_NOT_EXISTS = "Mot Testing Certificate for group '%value%' already exist";

    private $repository;
    private $personId;

    public function __construct(QualificationAwardRepository $repository, $personId, $options = null)
    {
        parent::__construct($options);

        $this->repository = $repository;
        $this->personId = $personId;
    }

    protected $messageTemplates = array(
        self::MSG_NOT_FOUND => self::ERROR_NOT_EXISTS,
    );

    public function isValid($value)
    {
        $this->setValue($value);

        try {
            $this->repository->getOneByGroupAndPersonId($value, $this->personId);
        } catch (NotFoundException $e) {
            return true;
        }

        $this->error(self::MSG_NOT_FOUND);

        return false;
    }
}
