<?php

namespace PersonApi\Service;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\Person;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;

class PersonQualificationStatusService implements AutoWireableInterface
{
    private $authorisationForTestingMotRepository;
    private $authorisationForTestingMotStatusRepository;
    private $vehicleClassRepository;

    public function __construct(
        AuthorisationForTestingMotRepository $authorisationForTestingMotRepository,
        AuthorisationForTestingMotStatusRepository $authorisationForTestingMotStatusRepository,
        VehicleClassRepository $vehicleClassRepository
    ) {
        $this->authorisationForTestingMotRepository = $authorisationForTestingMotRepository;
        $this->authorisationForTestingMotStatusRepository = $authorisationForTestingMotStatusRepository;
        $this->vehicleClassRepository = $vehicleClassRepository;
    }

    public function changeStatus(Person $person, $vehicleClassGroup, $qualificationStatusCode)
    {
        $authorisations = $this->getAuthorisationsForGroup($person, $vehicleClassGroup);
        $status = $this->getQualificationStatus($qualificationStatusCode);

        if (!empty($authorisations)) {
            foreach ($authorisations as $authorisation) {
                $authorisation->setStatus($status);
                $this->authorisationForTestingMotRepository->persist($authorisation);
            }
        } else {
            foreach (VehicleClassGroup::getClassesForGroup($vehicleClassGroup) as $code) {
                $vehicleClass = $this->vehicleClassRepository->getByCode($code);
                $authorisation = new AuthorisationForTestingMot();
                $authorisation
                    ->setPerson($person)
                    ->setStatus($status)
                    ->setVehicleClass($vehicleClass);

                $this->authorisationForTestingMotRepository->persist($authorisation);
            }
        }

        $this->authorisationForTestingMotRepository->flush();
    }

    public function removeStatus(Person $person, $vehicleClassGroup)
    {
        $authorisations = $this->getAuthorisationsForGroup($person, $vehicleClassGroup);

        if (empty($authorisations)) {
            throw new \InvalidArgumentException("Cannot remove status for group '".$vehicleClassGroup."'");
        }

        foreach ($authorisations as $authorisation) {
            $this->authorisationForTestingMotRepository->remove($authorisation);
        }

        $this->authorisationForTestingMotRepository->flush();
    }

    private function getAuthorisationsForGroup(Person $person, $group)
    {
        $classesInGroup = VehicleClassGroup::getClassesForGroup($group);

        /** @var AuthorisationForTestingMot[] $authorisations */
        $authorisations = \DvsaCommon\Utility\ArrayUtils::filter(
            $person->getAuthorisationsForTestingMot(),
            function (AuthorisationForTestingMot $authorisationForTestingMot) use ($classesInGroup) {
                return in_array($authorisationForTestingMot->getVehicleClass(), $classesInGroup);
            }
        );

        return $authorisations;
    }

    private function getQualificationStatus($qualificationStatusCode)
    {
        return $this->authorisationForTestingMotStatusRepository->getByCode($qualificationStatusCode);
    }
}
