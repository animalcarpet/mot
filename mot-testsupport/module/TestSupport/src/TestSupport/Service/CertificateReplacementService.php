<?php

namespace TestSupport\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\CertificateReplacement;

class CertificateReplacementService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getCertificateReplacementType($motTestId)
    {
        // using SQL for this, as DQL requires write access to Doctrine proxy_dir and also could not find mapping annotations
        $sql = 'SELECT ct.code FROM certificate_replacement cr JOIN certificate_type ct ON cr.certificate_type_id = ct.id WHERE cr.mot_test_id = :motTestId';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('motTestId', $motTestId);
        $query->execute();

        return $query->fetchColumn(0);
    }
}
