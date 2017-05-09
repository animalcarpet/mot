<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Configuration;

/**
 * TODO: move to common api!
 * Repository for Configuration objects
 * Class ConfigurationRepository.
 *
 * @codeCoverageIgnore
 */
class ConfigurationRepository extends EntityRepository implements ConfigurationRepositoryInterface
{
    /**
     * Retrieves a configuration parameter by key.
     *
     * @param string $paramKey
     *
     * @return string
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getValue($paramKey)
    {
        /** @var Configuration $config */
        $config = $this->findOneBy(['key' => $paramKey]);
        if (is_null($config)) {
            throw new NotFoundException('Configuration parameter', $paramKey);
        }

        return $config->getValue();
    }
}
