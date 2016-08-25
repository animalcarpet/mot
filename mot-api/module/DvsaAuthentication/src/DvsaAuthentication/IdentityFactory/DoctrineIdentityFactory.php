<?php

namespace DvsaAuthentication\IdentityFactory;

use DvsaAuthentication\Identity;
use DvsaAuthentication\IdentityFactory;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle;

class DoctrineIdentityFactory implements IdentityFactory
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * @param PersonRepository $personRepository
     */
    public function __construct(PersonRepository $personRepository, FeatureToggles $featureToggles)
    {
        $this->personRepository = $personRepository;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param string $username
     * @param string $token
     * @param string $uuid
     * @param \DateTime $passwordExpiryDate
     *
     * @return Identity
     */
    public function create($username, $token, $uuid, $passwordExpiryDate)
    {
        $person = $this->personRepository->findIdentity($username);

        if (!$person instanceof Person) {
            throw new \InvalidArgumentException(sprintf('Person "%s" not found', $username));
        }

        $identity = (new Identity($person))->setToken($token)->setUuid($uuid)->setPasswordExpiryDate($passwordExpiryDate);

        if(!$this->featureToggles->isEnabled(FeatureToggle::TWO_FA)) {
            $identity->setIsSecondFactorRequired(false);
        }

        return $identity;
    }
}