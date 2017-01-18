<?php
namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;

class DocumentService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    public function get($id)
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        $document = $connection->fetchAll("SELECT * FROM jasper_document WHERE id = :id", [
            'id' => $id
        ]);

        return $document;
    }

    public function delete($id)
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        $connection->executeQuery("UPDATE mot_test_current SET document_id = NULL WHERE document_id = :id", [
            'id' => $id
        ]);

        $result = $connection->executeQuery("DELETE FROM jasper_document WHERE id = :id", [
            'id' => $id
        ]);

        return $result;
    }
}