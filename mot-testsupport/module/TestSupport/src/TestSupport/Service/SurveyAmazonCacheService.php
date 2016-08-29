<?php

namespace TestSupport\Service;

use Aws\S3\S3Client;
use TestSupport\Helper\TestDataResponseHelper;
use Zend\View\Model\JsonModel;

class SurveyAmazonCacheService
{
    const PREFIX = '%s/';

    private $env;
    private $s3Client;
    private $bucket;

    public function __construct(
        S3Client $s3Client,
        $bucket,
        $env
    ) {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
        $this->env = $env;
    }

    /**
     * @return string
     */
    private function getPrefix()
    {
        return sprintf(self::PREFIX, $this->env);
    }

    /**
     * @param string $storageKey
     */
    private function deleteObject($storageKey)
    {
        $this->s3Client->deleteObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $storageKey,
            ]
        );
    }

    /**
     * @return JsonModel
     * @throw S3Exception
     */
    public function removeAll()
    {
        $command = $this->s3Client->getCommand(
            'ListObjects', [
                'Bucket' => $this->bucket,
                'Prefix' => $this->getPrefix(),
            ]
        );

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
