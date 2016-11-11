<?php

namespace DvsaMotApi\Factory\Service;

use DataCatalogApi\Service\DataCatalogService;
use DvsaMotApi\Domain\DvsaContactDetails\DvsaContactDetailsConfiguration;
use DvsaMotApi\Service\CertificateCreationService;
use UnexpectedValueException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CertificateCreationServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        if (!isset($config['dvsa_contact_details']) || !is_array($config['dvsa_contact_details'])) {
            throw new UnexpectedValueException('Key "dvsa_contact_details" is either missing from the config or not an array.');
        }
        $dvsaContactDetailsConfiguration = new DvsaContactDetailsConfiguration($config['dvsa_contact_details']);

        return new CertificateCreationService(
            $serviceLocator->get('MotTestService'),
            $serviceLocator->get('DocumentService'),
            $serviceLocator->get(DataCatalogService::class),
            $dvsaContactDetailsConfiguration
        );
    }
}
