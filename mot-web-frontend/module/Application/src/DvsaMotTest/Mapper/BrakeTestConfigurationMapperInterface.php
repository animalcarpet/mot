<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;

/**
 * Maps form data to BrakeTestConfiguration Dto
 */
interface BrakeTestConfigurationMapperInterface
{
    /**
     * @param MotTest $motTest
     * @param string $vehicleClass
     *
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDefaultDto(MotTest $motTest, $vehicleClass = null);

    /**
     * @param array $data
     *
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDto($data);
}
