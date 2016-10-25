<?php

namespace DataCatalogApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\BodyType;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\DvlaMakeModelMap;
use DvsaEntities\Entity\EmptyVinReason;
use DvsaEntities\Entity\EmptyVrmReason;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\TransmissionType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\WeightSource;
use DvsaEntities\Repository\BodyTypeRepository;
use DvsaEntities\Repository\ColourRepository;
use DvsaEntities\Repository\CountryOfRegistrationRepository;
use DvsaEntities\Repository\EmptyVinReasonRepository;
use DvsaEntities\Repository\EmptyVrmReasonRepository;
use DvsaEntities\Repository\FuelTypeRepository;
use DvsaEntities\Repository\MakeRepository;
use DvsaEntities\Repository\ModelDetailRepository;
use DvsaEntities\Repository\ModelRepository;
use DvsaEntities\Repository\TransmissionTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEntities\Repository\WeightSourceRepository;

/**
 * Provides access to all vehicle feature related entities like make, model, fuelType, etc.
 * A class created to reduce the number of dependencies needed to inject in the context
 * of vehicle related operations.
 * For entities.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VehicleCatalogService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function findMakeByName($name)
    {
        return $this->makeRepository()->findByName($name);
    }

    /**
     * @param string $code
     *
     * @return \DvsaEntities\Entity\Make
     */
    public function getMake($code)
    {
        return $this->makeRepository()->getByCode($code);
    }

    /**
     * @param string $code
     *
     * @return Make|null
     */
    public function findMakeByCode($code)
    {
        return $this->makeRepository()->findOneBy(["code" => $code]);
    }

    /**
     * * @return \DvsaEntities\Entity\Make[]
     */
    public function getMakes()
    {
        return $this->makeRepository()->findBy([ 'isVerified' => 1, 'isSelectable' => 1 ], ['name' => 'ASC']);
    }

    /**
     * @param int $modelId
     *
     * @return \DvsaEntities\Entity\Model
     */
    public function getModelById($modelId)
    {
        return $this->modelRepository()->findOneBy([ 'id' => $modelId ]);
    }

    /**
     * @param string $name
     * @param string $makeId
     *
     * @return array
     */
    public function findModelByNameAndCodeId($name, $makeId)
    {
        return $this->modelRepository()->findByNameForMake($name, $makeId);
    }

    /**
     * @param string $makeCode
     *
     * @return \DvsaEntities\Entity\Model[]
     */
    public function getModelsByMake($makeCode)
    {
        return $this->modelRepository()->getByMake($makeCode);
    }

    /**
     * @param string $makeId
     *
     * @return \DvsaEntities\Entity\Model[]
     */
    public function getModelsByMakeId($makeId)
    {
        return $this->modelRepository()->getByMakeId($makeId);
    }

    /**
     * @param string $makeId
     * @param string $modelCode
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Model
     */
    public function getModel($makeId, $modelCode)
    {
        return $this->modelRepository()->getByCode($makeId, $modelCode);
    }

    /**
     * @param string $makeCode
     * @param string $modelCode
     *
     * @return Model|null
     */
    public function findModel($makeCode, $modelCode)
    {
        return $this->modelRepository()->findOneByMakeAndModel($makeCode, $modelCode);
    }

    public function getModelDetail($id, $refOnly = false)
    {
        return $refOnly
            ? $this->modelDetailRepository()->getReference($id)
            :
            $this->modelDetailRepository()->get($id);
    }

    /**
     * @param $makeCode
     * @param $modelCode
     *
     * @return \DvsaEntities\Entity\ModelDetail[]
     */
    public function getModelDetailsByModel($makeCode, $modelCode)
    {
        return $this->modelDetailRepository()->getByModel($makeCode, $modelCode);
    }

    /**
     * @param integer $code
     *
     * @return \DvsaEntities\Entity\FuelType
     */
    public function getFuelType($code)
    {
        return $this->fuelTypeRepository()->getByCode($code);
    }

    /**
     * @param string $code
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return \DvsaEntities\Entity\FuelType
     */
    public function getFuelTypeByCode($code)
    {
        return $this->fuelTypeRepository()->getByCode($code);
    }

    /**
     * @param string $code
     *
     * @return \DvsaEntities\Entity\FuelType|null
     */
    public function findFuelTypeByPropulsionCode($code)
    {
        return $this->fuelTypeRepository()->findOneByDvlaPropulsionCode($code);
    }

    /**
     * @param string $id
     * @param bool   $refOnly
     *
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration($id, $refOnly = false)
    {
        return $refOnly ? $this->countryOfRegRepository()->getReference($id)
            : $this->countryOfRegRepository()->get($id);
    }

    /**
     * @param string $code
     *
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistrationByCode($code)
    {
        return $this->countryOfRegRepository()->getByCode($code);
    }

    /**
     * Returns a DvlaMakeModelMap entity with a reference to a Model and Make entities.
     *
     * @param string $dvlaMakeCode
     * @param string $dvlaModelCode
     *
     * @return DvlaMakeModelMap|null
     */
    public function getMakeModelMapByDvlaCode($dvlaMakeCode, $dvlaModelCode)
    {
        $map = $this
            ->entityManager
            ->getRepository(DvlaMakeModelMap::class)
            ->findOneBy([
                'dvlaMakeCode'  => $dvlaMakeCode,
                'dvlaModelCode' => $dvlaModelCode,
            ]);

        return $map;
    }

    /**
     * Retrieves DVLA Make name by Code.
     *
     * @param $dvlaMakeCode
     *
     * @return bool|string
     */
    public function getMakeNameByDvlaCode($dvlaMakeCode)
    {
        $sql = "SELECT name FROM dvla_make WHERE code = :code LIMIT 1";

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindParam(':code', $dvlaMakeCode);
        $stmt->execute();
        $make = $stmt->fetchColumn();

        return $make;
    }

    /**
     * Retrieved DVLA Model name by Code.
     *
     * @param $dvlaMakeCode
     * @param $dvlaModelCode
     *
     * @return bool|string
     */
    public function getModelNameByDvlaCode($dvlaMakeCode, $dvlaModelCode)
    {
        $sql = "SELECT name FROM dvla_model WHERE code = :modelCode AND make_code = :makeCode LIMIT 1";

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindParam(':modelCode', $dvlaModelCode);
        $stmt->bindParam(':makeCode', $dvlaMakeCode);
        $stmt->execute();
        $make = $stmt->fetchColumn();

        return $make;
    }

    /**
     * @param string $code
     *
     * @return Make
     */
    public function getMakeByCode($code)
    {
        return $this->makeRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param string $code
     *
     * @return Model
     */
    public function getModelByCode($code)
    {
        return $this->modelRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param string $code
     *
     * @return BodyType
     */
    public function getBodyTypeByCode($code)
    {
        return $this->bodyTypeRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param int  $id
     * @param bool $refOnly
     *
     * @return \DvsaEntities\Entity\Colour
     */
    public function getColour($id, $refOnly = false)
    {
        return $refOnly ? $this->colourRepository()->getReference($id) : $this->colourRepository()->get($id);
    }

    /**
     * @param string $code
     *
     * @return Colour|null
     */
    public function findColourByCode($code)
    {
        return $this->colourRepository()->findOneByCode($code);
    }

    /**
     * @param string $code
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Colour
     */
    public function getColourByCode($code)
    {
        return $this->colourRepository()->getByCode($code);
    }

    /**
     * @param      $id
     * @param bool $refOnly
     *
     * @return \DvsaEntities\Entity\TransmissionType
     */
    public function getTransmissionType($id, $refOnly = false)
    {
        return $refOnly ? $this->transmissionTypeRepository()->getReference($id)
            : $this->transmissionTypeRepository()->get($id);
    }

    /**
     * @param      $id
     * @param bool $refOnly
     *
     * @return \DvsaEntities\Entity\BodyType
     */
    public function getBodyType($id, $refOnly = false)
    {
        return $refOnly ? $this->bodyTypeRepository()->getReference($id)
            : $this->bodyTypeRepository()->get($id);
    }

    /**
     * @param string $code
     *
     * @return \DvsaEntities\Entity\EmptyVinReason
     */
    public function getEmptyVinReasonByCode($code)
    {
        return $this->emptyVinReasonRepository()->getByCode($code);
    }

    /**
     * @param string $code
     *
     * @return \DvsaEntities\Entity\EmptyVrmReason
     */
    public function getEmptyVrmReasonByCode($code)
    {
        return $this->emptyVrmReasonRepository()->getByCode($code);
    }

    /**
     * @param string $code
     *
     * @return \DvsaEntities\Entity\BodyType|null
     */
    public function findBodyTypeByCode($code)
    {
        return $this->bodyTypeRepository()->findOneByCode($code);
    }

    /**
     * @param string $vehicleClassCode Vehicle Class Code
     *
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClassByCode($vehicleClassCode)
    {
        return $this->vehicleClassRepository()->getByCode($vehicleClassCode);
    }

    /**
     * @param integer $id      Vehicle Class Id
     * @param bool    $refOnly
     *
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClass($id, $refOnly = false)
    {
        $repository = $this->vehicleClassRepository();

        return $refOnly
            ? $repository->getReference($id)
            : $repository->get($id);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     *
     * @return \DvsaEntities\Entity\WeightSource
     */
    public function getWeightSourceByCode($code)
    {
        return $this->weightSourceRepository()->getByCode($code);
    }

    /** @return MakeRepository */
    private function makeRepository()
    {
        return $this->entityManager->getRepository(Make::class);
    }

    /** @return WeightSourceRepository */
    private function weightSourceRepository()
    {
        return $this->entityManager->getRepository(WeightSource::class);
    }

    /** @return ModelRepository */
    private function modelRepository()
    {
        return $this->entityManager->getRepository(Model::class);
    }

    /** @return ModelDetailRepository */
    private function modelDetailRepository()
    {
        return $this->entityManager->getRepository(ModelDetail::class);
    }

    /** @return FuelTypeRepository */
    private function fuelTypeRepository()
    {
        return $this->entityManager->getRepository(FuelType::class);
    }

    /** @return TransmissionTypeRepository */
    private function transmissionTypeRepository()
    {
        return $this->entityManager->getRepository(TransmissionType::class);
    }

    /**
     * @return BodyTypeRepository
     */
    private function bodyTypeRepository()
    {
        return $this->entityManager->getRepository(BodyType::class);
    }

    /** @return ColourRepository */
    private function colourRepository()
    {
        return $this->entityManager->getRepository(Colour::class);
    }

    /** @return CountryOfRegistrationRepository */
    private function countryOfRegRepository()
    {
        return $this->entityManager->getRepository(CountryOfRegistration::class);
    }

    /** @return vehicleClassRepository */
    private function vehicleClassRepository()
    {
        return $this->entityManager->getRepository(VehicleClass::class);
    }

    /** @return EmptyVinReasonRepository */
    private function emptyVinReasonRepository()
    {
        return $this->entityManager->getRepository(EmptyVinReason::class);
    }

    /** @return EmptyVrmReasonRepository */
    private function emptyVrmReasonRepository()
    {
        return $this->entityManager->getRepository(EmptyVrmReason::class);
    }
}
