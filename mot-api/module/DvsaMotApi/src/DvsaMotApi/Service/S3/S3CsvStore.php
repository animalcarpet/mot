<?php

namespace DvsaMotApi\Service\S3;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3CsvStore implements FileStorageInterface
{
    const NO_SUCH_KEY_ERROR_CODE = 'NoSuchKey';

    /** @var S3Client $s3Client */
    private $s3Client;

    /** @var string $bucket */
    private $bucket;

    /** @var string $rootFolder */
    private $rootFolder;

    /**
     * S3CsvStore constructor.
     *
     * @param S3Client $s3Client
     * @param string   $bucket
     * @param string   $rootFolder
     */
    public function __construct(
        S3Client $s3Client,
        $bucket,
        $rootFolder
    ) {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
        $this->rootFolder = $rootFolder;
    }

    /**
     * @param array  $columns
     * @param array  $values
     * @param string $key
     *
     * @return Result
     */
    public function putFile(array $columns, array $values, $key)
    {
        $fullKeyPath = $this->getFullPath($key);
        $csvHandle = fopen('php://memory', 'r+');
        if (null !== $columns) {
            fputcsv($csvHandle, $columns);
        }
        if (null !== $values) {
            fputcsv($csvHandle, $values);
        }
        rewind($csvHandle);

        $result = $this->s3Client->putObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $fullKeyPath,
                'ContentType' => 'application/csv',
                'Body' => stream_get_contents($csvHandle),
            ]
        );

        fclose($csvHandle);

        return $result;
    }

    /**
     * @return Result[]
     */
    public function getAllFiles()
    {
        $results = [];

        $objects = $this->s3Client->getIterator(
            'ListObjects', [
                'Bucket' => $this->bucket,
                'Prefix' => $this->rootFolder,
            ]
        );

        foreach ($objects as $object) {
            array_push($results, $object);
        }

        return $results;
    }

    /**
     * @param string $fullPath
     *
     * @returns string
     *
     * @throws S3Exception
     */
    public function getFile($fullPath)
    {
        try {
            // load contents of file into memory
            $object = (string) $this->s3Client->getObject(
                [
                    'Bucket' => $this->bucket,
                    'Key' => $fullPath,
                ]
            )['Body'];

            return $object;
        } catch (S3Exception $ex) {
            if ($ex->getCode() == self::NO_SUCH_KEY_ERROR_CODE) {
                return;
            } else {
                throw $ex;
            }
        }
    }

    /**
     * Build a key that is preceded with root folder.
     *
     * @param $key
     *
     * @return string
     */
    private function getFullPath($key)
    {
        return $this->rootFolder.'/'.$key;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function stripRootFolderFromKey($path)
    {
        $key = str_replace($this->rootFolder.'/', '', $path);

        return $key;
    }
}
