<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Doctrine\ORM\EntityManager;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\Entity;

/**
 * Class AbstractPersistableService.
 */
class AbstractPersistableService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var
     */
    protected $logger;

    /**
     * @param EntityManager $entityManager
     * @param Logger        $logger
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger = null
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Persist and save the given entity.
     *
     * @param Entity $entity
     * @param bool   $isTransactional Default to false
     */
    protected function save(Entity $entity, $isTransactional = false)
    {
        if ($isTransactional) {
            $this->inTransaction(
                function () use ($entity) {
                    $this->persistAndFlush($entity);
                }
            );
        } else {
            $this->persistAndFlush($entity);
        }
    }

    /**
     * @return string
     */
    protected function getDetailsStepName()
    {
        return ValidatorKeyConverter::inputFilterToStep(DetailsInputFilter::class);
    }

    /**
     * @return string
     */
    protected function getAddressStepName()
    {
        return ValidatorKeyConverter::inputFilterToStep(AddressInputFilter::class);
    }

    /**
     * @return string
     */
    protected function getPasswordStepName()
    {
        return ValidatorKeyConverter::inputFilterToStep(PasswordInputFilter::class);
    }

    /**
     * @return string
     */
    protected function getSecurityQuestionFirstStepName()
    {
        return ValidatorKeyConverter::inputFilterToStep(SecurityQuestionFirstInputFilter::class);
    }

    /**
     * @return string
     */
    protected function getSecurityQuestionSecondStepName()
    {
        return ValidatorKeyConverter::inputFilterToStep(SecurityQuestionSecondInputFilter::class);
    }

    private function persistAndFlush($entity)
    {
        $this->entityManager->getConnection()->exec("SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data')");
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
