<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;

/**
 * Maps form data to BrakeTestConfiguration Dto.
 */
interface BrakeTestConfigurationMapperInterface
{
    /**
     * @param MotTest $motTest
     *
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDefaultDto(MotTest $motTest, DvsaVehicle $vehicle = null);

    /**
     * @param array $data
     *
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDto($data);
}
