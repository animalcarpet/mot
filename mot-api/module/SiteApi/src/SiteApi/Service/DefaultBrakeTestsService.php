<?php

namespace SiteApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;

/**
 * Saves the brake test defaults to site entity.
 */
class DefaultBrakeTestsService
{
    private $siteRepository;
    private $validator;
    private $authService;
    private $brakeTestTypeRepository;

    public function __construct(
        SiteRepository $siteRepository,
        BrakeTestTypeRepository $brakeTestTypeRepository,
        BrakeTestConfigurationValidator $brakeTestConfigurationValidator,
        AuthorisationServiceInterface $authService
    ) {
        $this->siteRepository = $siteRepository;
        $this->brakeTestTypeRepository = $brakeTestTypeRepository;
        $this->validator = $brakeTestConfigurationValidator;
        $this->authService = $authService;
    }

    public function put($id, $data)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE, $id);

        $site = $this->siteRepository->get($id);

        $this->updateDefaultBrakeTestTypes($site, $data);
        $this->siteRepository->save($site);
    }

    private function updateDefaultBrakeTestTypes(Site $site, $data)
    {
        if (isset($data['defaultBrakeTestClass1And2'])) {
            $this->validator->validateBrakeTestTypeClass1And2($data['defaultBrakeTestClass1And2']);
            /** @var BrakeTestType $brakeTestType */
            $brakeTestType = $this->brakeTestTypeRepository->getByCode($data['defaultBrakeTestClass1And2']);
            $site->setDefaultBrakeTestClass1And2($brakeTestType);
        }
        if (isset($data['defaultParkingBrakeTestClass3AndAbove'])) {
            $site->setDefaultParkingBrakeTestClass3AndAbove(
                $this->getBrakeTestTypeByCode($data['defaultParkingBrakeTestClass3AndAbove'])
            );
        }
        if (isset($data['defaultServiceBrakeTestClass3AndAbove'])) {
            $site->setDefaultServiceBrakeTestClass3AndAbove(
                $this->getBrakeTestTypeByCode($data['defaultServiceBrakeTestClass3AndAbove'])
            );
        }
    }

    /**
     * @param string $code
     *
     * @return BrakeTestType
     *
     * @throws \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function getBrakeTestTypeByCode($code)
    {
        $this->validator->validateBrakeTestTypeClass3AndAbove($code);

        return $this->brakeTestTypeRepository->getByCode($code);
    }
}
