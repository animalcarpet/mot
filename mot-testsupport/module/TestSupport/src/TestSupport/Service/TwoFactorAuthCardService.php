<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestDataResponseHelper;
use Doctrine\ORM\EntityManager;
use Exception;

class TwoFactorAuthCardService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    const ENCRYPTED_SECRET = '-----BEGIN PGP MESSAGE-----
Version: BCPG v1.46

jA0EAwMCZIsq2TVkHiZgyUZvaApIr4zLJu52omoor/JIGIc211J3fm41+jYVvD6j
1AF2NAuEM16S3S2JwHunFVqD6vKwKsI4spYMrPK69yIMJmqc6rvR
=GW7j
-----END PGP MESSAGE-----';

    const DECRYPTED_SECRET = '9b0bf51be1fdd18842209e430092d643b28768b7';

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create()
    {
        $serialNumber = $this->getRandomisedSerialNumber();

        $stmt = $this->entityManager->getConnection()->prepare("
            INSERT INTO security_card (serial_number, secret, security_card_status_lookup_id, created_by)
            VALUES (
                :serialNumber, 
                :secret,
                (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'),
                (SELECT `id` FROM `person` WHERE `username` = 'static data')
            )
        ");
        $stmt->bindValue(':serialNumber', $serialNumber);
        $stmt->bindValue(':secret', self::ENCRYPTED_SECRET);

        if (!$stmt->execute()) {
            throw new Exception('Could not save new security card');
        }

        return TestDataResponseHelper::jsonOk([
            'serialNumber' => $serialNumber,
            'secret' => self::DECRYPTED_SECRET
        ]);
    }

    private function getRandomisedSerialNumber()
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4) . rand(10000000, 99999999);
    }
}