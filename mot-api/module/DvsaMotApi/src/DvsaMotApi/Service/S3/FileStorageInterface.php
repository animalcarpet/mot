<?php

namespace DvsaMotApi\Service\S3;

use Aws\Result;

interface FileStorageInterface
{
    /**
     * @param array  $columns
     * @param array  $values
     * @param string $key
     *
     * @returns Result
     */
    public function putFile(array $columns, array $values, $key);

    /**
     * @return Result[]
     */
    public function getAllFiles();

    /**
     * @param string $fileKey
     *
     * @return string
     */
    public function getFile($fileKey);
}
