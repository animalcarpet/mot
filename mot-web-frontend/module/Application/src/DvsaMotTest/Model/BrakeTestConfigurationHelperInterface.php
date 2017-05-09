<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;

/**
 * Interface for Brake Test Config Helpers.
 */
interface BrakeTestConfigurationHelperInterface
{
    /**
     * @return BrakeTestConfigurationDtoInterface
     */
    public function getConfigDto();

    /**
     * @param BrakeTestConfigurationDtoInterface $configDto
     */
    public function setConfigDto(BrakeTestConfigurationDtoInterface $configDto);
}
