<?php

namespace DvsaCommonApi\Service;

use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Model\OutputFormat;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;

/**
 * Class AbstractSearchService.
 */
abstract class AbstractSearchService extends AbstractService
{
    /**
     * Perform the search.
     *
     * @param SearchParam $params
     * @param             $format
     *
     * @return mixed
     */
    public function search(SearchParam $params, OutputFormat $format)
    {
        $this->checkPermissions();

        $this->checkParams($params);

        return $this->repositorySearch($params, $format);
    }

    /**
     * Provides an entry to get the search params for a service.
     *
     * @SuppressWarnings(unused)
     *
     * @param null $values
     *
     * @throws \Exception
     */
    public function getSearchParams($values = null)
    {
        throw new \Exception('Override in base class');
    }

    /**
     * @SuppressWarnings(unused)
     *
     * @param $searchParams
     *
     * @throws \Exception
     */
    public function getOutputFormat($searchParams)
    {
        throw new \Exception('Override in base class');
    }

    /**
     * Provides the ability to check the users access to the current search.
     */
    abstract protected function checkPermissions();

    /**
     * Provides the service with the opportunity to validate the param settings.
     *
     * @param $params
     *
     * @return mixed
     *
     * @throws BadRequestExceptionWithMultipleErrors
     */
    public function checkParams($params)
    {
        $fieldErrors = [];

        if ($params->getRowCount() <= 0) {
            $fieldErrors[] = new ErrorMessage(
                'Row count must be 1 or more',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                array('siteNumber' => null)
            );
        }

        // Send back any errors we have found
        if ($fieldErrors) {
            throw new BadRequestExceptionWithMultipleErrors([], $fieldErrors);
        }
    }

    /**
     * Performs the actual search using the repository.
     *
     * @param SearchParam  $params
     * @param OutputFormat $format
     *
     * @return mixed
     */
    abstract protected function repositorySearch(SearchParam $params, OutputFormat $format);
}
