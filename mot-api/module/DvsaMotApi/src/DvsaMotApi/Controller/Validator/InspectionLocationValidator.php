<?php

namespace DvsaMotApi\Controller\Validator;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class InspectionLocationValidator.
 *
 * Performs validation for an inspection location data bundle. The service may be omitted in which case
 * only the presence / absence of the fields can be checked. If the service is given then it will verify
 * that the sit-id part of the string points to a valid record.
 *
 * This class does not produce error messages, that is within the domain of the call site. Instead it will
 * create bit masks for the various types of errors that it can detect.
 */
class InspectionLocationValidator
{
    const FIELD_SITEID = 'siteid';
    const FIELD_LOCATION = 'location';

    const ERROR_SITE_ID_REQUIRED = 0x01;
    const ERROR_SITE_ID_NOT_VALID = 0x02;
    const ERROR_SITE_OR_LOCATION = 0x04;

    const ERROR_MSG_SITE_NUMBER_REQUIRED = 'Please enter a Site Id or Location';
    const ERROR_MSG_SITE_ID_INCORRECT = 'Site Id is incorrect';

    protected $keySiteId;
    protected $keyLocation;
    protected $validSiteId;

    /**
     * @param string $siteKey     String contains the key for the siteid number
     * @param string $locationKey String contains the free-text location content
     */
    public function __construct($siteKey = self::FIELD_SITEID, $locationKey = self::FIELD_LOCATION)
    {
        $this->keySiteId = $siteKey;
        $this->keyLocation = $locationKey;
        $this->validSiteId = null;
        $this->validSiteName = null;
        $this->validLocation = null;
    }

    /**
     * Ensures that either a ite-id or location text string are given and if a service
     * handler was passed, that the site id was in fact a valid one.
     *
     * @param $data        Array data to be validated
     * @param $vtsService  \SiteApi\Service\SiteService  for MOT verification
     *
     * @throws BadRequestException
     */
    public function validate($data, $vtsService = null)
    {
        $isSite = ArrayUtils::hasNotEmptyValue($data, $this->keySiteId);
        $isLoc = ArrayUtils::hasNotEmptyValue($data, $this->keyLocation);

        // if both are empty or both are given...
        if (($isSite && $isLoc) || (!$isSite && !$isLoc)) {
            throw new BadRequestException(
                self::ERROR_MSG_SITE_NUMBER_REQUIRED,
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }

        if ($isSite && !$isLoc) {
            $this->validateSiteNumber(
                $vtsService,
                $data[$this->keySiteId]
            );
        }

        $this->validLocation = ArrayUtils::tryGet($data, $this->keyLocation);
    }

    /**
     * If a site number was extracted and a service is available then we can ensure that a valid
     * site number has actually been passed in.
     *
     * @param $vtsService  \SiteApi\Service\SiteService  for MOT verification
     * @param $siteNumber  String contains the site number to be validated
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    protected function validateSiteNumber($vtsService, $siteNumber)
    {
        if ($vtsService) {
            $siteIdValid = false;

            // TODO: Refactor somewhere else, this regex has been used  twice now for this.
            if (1 === preg_match('/^\w+$/', $siteNumber, $match)) {
                try {
                    $site = $vtsService->getSiteBySiteNumber($match[0]);
                    $errorMsg = self::ERROR_SITE_ID_NOT_VALID;

                    $this->validSiteId = $site->getId();
                    $this->validSiteName = $site->getName();
                    $siteIdValid = true;
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {
                $errorMsg = self::ERROR_MSG_SITE_ID_INCORRECT;
            }

            if (!$siteIdValid) {
                throw new BadRequestException(
                    $errorMsg,
                    BadRequestException::ERROR_CODE_INVALID_DATA,
                    $errorMsg
                );
            }
        }
    }

    /**
     * @return int site id or null if it was not set or validated
     */
    public function getSiteId()
    {
        return $this->validSiteId;
    }

    /**
     * @return string|null the free text that was entered or null
     */
    public function getLocation()
    {
        return $this->validLocation;
    }

    /**
     * The internal name from the POST data we use for the site id value.
     *
     * @return string
     */
    public function getSiteIdKey()
    {
        return $this->keySiteId;
    }

    /**
     * The internal name from the POST data we use for the free text comment.
     *
     * @return string
     */
    public function getLocationKey()
    {
        return $this->keyLocation;
    }

    /**
     * @return string the site name or null if it was not used / validated
     */
    public function getSiteName()
    {
        return $this->validSiteName;
    }
}
