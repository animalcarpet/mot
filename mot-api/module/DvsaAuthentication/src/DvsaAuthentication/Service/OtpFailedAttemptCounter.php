<?php

namespace DvsaAuthentication\Service;

use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\ConfigurationRepositoryInterface;
use DvsaEntities\Repository\PersonRepository;

class OtpFailedAttemptCounter
{
    const CONFIG_PARAM_OTP_MAX_NUMBER_OF_ATTEMPTS = 'otpMaxNumberOfAttempts';

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var ConfigurationRepositoryInterface
     */
    private $configurationRepository;

    /**
     * @var null|int
     */
    private $maxAttempts;

    /**
     * @param PersonRepository        $personRepository
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(PersonRepository $personRepository, ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->personRepository = $personRepository;
        $this->configurationRepository = $configurationRepository;
    }

    public function attemptSucceeded(Person $person)
    {
        if ($person->getOtpFailedAttempts() > 0) {
            $this->updateFailedAttempts($person, 0);
        }
    }

    public function attemptFailed(Person $person)
    {
        $this->updateFailedAttempts($person, $person->getOtpFailedAttempts() + 1);
    }

    /**
     * @param Person $person
     * @param int    $attempts
     */
    protected function updateFailedAttempts(Person $person, $attempts)
    {
        $person->setOtpFailedAttempts($attempts);
        $this->personRepository->persist($person);
        $this->personRepository->flush($person);
    }

    /**
     * @return int
     */
    public function getMaxAttempts()
    {
        if (null === $this->maxAttempts) {
            $this->maxAttempts = $this->fetchMaxAttempts();
        }

        return $this->maxAttempts;
    }

    /**
     * @param Person $person
     *
     * @return int
     */
    public function getLeftAttempts(Person $person)
    {
        $failedAttempts = $this->getFailedAttempts($person);
        $maxAttempts = $this->getMaxAttempts();
        $leftAttempts = $maxAttempts - $failedAttempts;

        return $leftAttempts >= 0 ? $leftAttempts : 0;
    }

    /**
     * @param Person $person
     *
     * @return int
     */
    private function getFailedAttempts(Person $person)
    {
        return $person->getOtpFailedAttempts() ?: 0;
    }

    /**
     * @return int
     */
    private function fetchMaxAttempts()
    {
        return (int) $this->configurationRepository->getValue(
            self::CONFIG_PARAM_OTP_MAX_NUMBER_OF_ATTEMPTS
        );
    }
}
