<?php

namespace TestSupport\Service;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use TestSupport\Helper\TestDataResponseHelper;
use Zend\Json\Json;

class StatisticsAmazonCacheService
{
    const PREFIX = '%s/';

    private $env;
    private $s3Client;
    private $bucket;

    public function __construct(S3Client $s3Client,
                                $bucket,
                                $env)
    {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
        $this->env = $env;
    }

    private function getPrefix()
    {
        return sprintf(self::PREFIX, $this->env);
    }

    private function deleteObject($storageKey)
    {
        $this->s3Client->deleteObject(
            array(
                'Bucket' => $this->bucket,
                'Key'    => $storageKey,
            )
        );
    }

    /**
     * @throw S3Exception
     */
    public function removeAll()
    {
        $command = $this->s3Client->getCommand('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->getPrefix(),
        ]);

        $objects = $this->s3Client->execute($command)['Contents'];

        if ($objects === null) {
            return TestDataResponseHelper::jsonOk();
        }

        foreach ($objects as $object) {
            $this->deleteObject($object['Key']);
        }

        return TestDataResponseHelper::jsonOk();
    }
}
