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
     * @param DvsaVehicle $vehicle - DvsaVehicle is needed for class 3+ to fetch vehicle weight,
     *          but it's not needed for classes 1 and 2 - therefore null default value for the second param.
     *          Ideally this interface should be split to accommodate those differences
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
