<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Validator\DrivingLicenceValidator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaEntities\Entity\Licence;
use DvsaEntities\Entity\LicenceCountry;
use DvsaEntities\Entity\LicenceType;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;

class LicenceDetailsService extends AbstractService
{
    const DRIVING_LICENCE_NUMBER = 'drivingLicenceNumber';
    const DRIVING_LICENCE_REGION = 'drivingLicenceRegion';
    const DRIVING_LICENCE_VALID_FROM = 'ValidFrom';
    const DRIVING_LICENCE_EXPIRY_DATE = 'ExpiryDate';
    const DRIVING_LICENCE_TYPE = 'LicenceType';

    /**
     * @var DrivingLicenceValidator
     */
    private $validator;

    /**
     * @var XssFilter
     */
    private $xssFilter;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    /**
     * @param EntityManager                         $entityManager
     * @param DrivingLicenceValidator               $validator
     * @param XssFilter                             $xssFilter
     * @param PersonDetailsChangeNotificationHelper $notificationHelper
     */
    public function __construct(
        EntityManager $entityManager,
        DrivingLicenceValidator $validator,
        XssFilter $xssFilter,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($entityManager);

        $this->validator = $validator;
        $this->xssFilter = $xssFilter;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param $personId
     * @param $data
     *
     * @return Licence
     *
     * @throws InvalidFieldValueException
     */
    public function updateOrCreate($personId, $data)
    {
        $person = $this->findPerson($personId);
        $licence = $person->getDrivingLicence();

        if (!$this->validator->isValid($data)) {
            throw new InvalidFieldValueException(implode(', ', $this->validator->getMessages()));
        }

        // uppercase the driving licence number
        $data[self::DRIVING_LICENCE_NUMBER] = strtoupper($data[self::DRIVING_LICENCE_NUMBER]);

        // add new licence
        if (null === $licence) {
            $licenceTypeRepo = $this->entityManager->getRepository(LicenceType::class);
            $licenceCountryRepo = $this->entityManager->getRepository(LicenceCountry::class);

            $licence = new Licence();
            $licence->setLicenceNumber($data[self::DRIVING_LICENCE_NUMBER])
                ->setCountry(
                    $licenceCountryRepo->getByCode($data[self::DRIVING_LICENCE_REGION])
                )
                ->setLicenceType(
                    $licenceTypeRepo->getByCode($data[self::DRIVING_LICENCE_TYPE])
                );

            $person->setDrivingLicence($licence);

            // update existing licence
        } else {
            $this->updateLicenceDetails($licence, $data);
        }

        $this->entityManager->flush();

        $this->sendChangeNotification($person);

        return $licence;
    }

    /**
     * Delete the licence associated with user $personId.
     *
     * @param int $personId
     */
    public function delete($personId)
    {
        $person = $this->findPerson($personId);
        $person->setDrivingLicence(null);

        $this->entityManager->flush();

        $this->sendChangeNotification($person);
    }

    /**
     * @param Licence $licence
     * @param array   $data
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function updateLicenceDetails(Licence $licence, array $data)
    {
        $licenceTypeRepo = $this->entityManager->getRepository(LicenceType::class);
        $licenceCountryRepo = $this->entityManager->getRepository(LicenceCountry::class);

        if ($data[self::DRIVING_LICENCE_NUMBER]) {
            $licence->setLicenceNumber($data[self::DRIVING_LICENCE_NUMBER]);
        }
        if ($data[self::DRIVING_LICENCE_REGION]) {
            $licence->setCountry(
                $licenceCountryRepo->getByCode($data[self::DRIVING_LICENCE_REGION])
            );
        }
        if ($data[self::DRIVING_LICENCE_VALID_FROM]) {
            $licence->setValidFrom($data[self::DRIVING_LICENCE_VALID_FROM]);
        }
        if ($data[self::DRIVING_LICENCE_EXPIRY_DATE]) {
            $licence->setExpiryDate($data[self::DRIVING_LICENCE_EXPIRY_DATE]);
        }
        if ($data[self::DRIVING_LICENCE_TYPE]) {
            $licence->setLicenceType(
                $licenceTypeRepo->getByCode($data[self::DRIVING_LICENCE_TYPE])
            );
        }
    }

    /**
     * @param $person
     */
    private function sendChangeNotification($person)
    {
        $this->notificationHelper->sendChangedPersonalDetailsNotification($person);
    }
}
