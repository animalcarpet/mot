<?php

namespace DvsaCommon\KeyValueStorage\S3;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;

class S3KeyValueStorage implements KeyValueStorageInterface
{
    const NO_SUCH_KEY_ERROR_CODE = 'NoSuchKey';

    private $rootFolder;

    private $s3Client;
    private $bucket;
    private $serializer;
    private $deserializer;

    public function __construct(
        S3Client $s3Client,
        $bucket,
        $rootFolder
    )
    {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
        $this->rootFolder = $rootFolder;
        $this->serializer = new DtoReflectiveSerializer();
        $this->deserializer = new DtoReflectiveDeserializer();
    }

    /**
     * @param $key
     * @throw S3Exception
     */
    public function delete($key)
    {
        $this->s3Client->deleteObject(
            array(
                'Bucket' => $this->bucket,
                'Key'    => $key,
            )
        );
    }

    public function getAsJsonArray($key)
    {
        $storageKey = $this->getFullPath($key);

        try {
            $object = $this->s3Client->getObject(
                array(
                    'Bucket'              => $this->bucket,
                    'Key'                 => $storageKey,
                    'ResponseContentType' => 'application/json',
                )
            )['Body'];
            return json_decode($object, true);
        } catch (S3Exception $ex) {
            if ($ex->getCode() == self::NO_SUCH_KEY_ERROR_CODE) {
                return null;
            } else {
                throw $ex;
            }
        }
    }

    public function getAsDto($key, $dtoClass)
    {
        $jsonArray = $this->getAsJsonArray($key);

        if ($jsonArray === null) {
            return null;
        }

        return $this->deserializer->deserialize($jsonArray, $dtoClass);
    }

    public function getAsDtoArray($key, $dtoClass)
    {
        $jsonArray = $this->getAsJsonArray($key);

        if ($jsonArray === null) {
            return null;
        }

        return $this->deserializer->deserializeArray($jsonArray, $dtoClass);
    }

    public function storeJsonArray($key, $json)
    {
        $storageKey = $this->getFullPath($key);

        $fileHandle = fopen('php://memory', 'r+');
        fputs($fileHandle, json_encode($json));
        rewind($fileHandle);

        $this->s3Client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $storageKey,
                'ContentType' => 'application/json',
                'Body'        => stream_get_contents($fileHandle),
            ]
        );

        fclose($fileHandle);
    }

    public function storeDto($key, $dto)
    {
        $jsonArray = $this->serializer->serialize($dto);

        $this->storeJsonArray($key, $jsonArray);
    }

    public function deleteAll($path)
    {
        $storagePath = $this->getFullPath($path);

        $command = $this->s3Client->getCommand('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $storagePath,
        ]);

        $objects = $this->s3Client->execute($command)['Contents'];

        if ($objects === null) {
            return;
        }

        foreach ($objects as $object) {
            $this->delete($object['Key']);
        }
    }

    /**
     * @param $keyPrefix
     * @throw S3Exception
     *
     * @return string[]
     */
    public function listKeys($keyPrefix = '')
    {
        $command = $this->s3Client->getCommand('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->rootFolder . '/' . $keyPrefix,
        ]);

        $objects = $this->s3Client->execute($command)['Contents'];

        $keys = ArrayUtils::map($objects, function (array $object) {
            return $object['Key'];
        });

        $keys = ArrayUtils::map($keys, function ($key) {
            return $this->stripRootFolderFromKey($key);
        });

        return $keys;
    }

    /**
     * Build a key that is preceded with root folder
     *
     * @param $key
     * @return string
     */
    private function getFullPath($key)
    {
        return $this->rootFolder . '/' . $key;
    }

    /**
     * Each key start with a root folder and dash.
     * This method will remove it from beginning of the key.
     *
     * @param $key
     * @return string
     */
    private function stripRootFolderFromKey($key)
    {
        return substr($key, 1 + strlen($this->rootFolder));
    }
}
