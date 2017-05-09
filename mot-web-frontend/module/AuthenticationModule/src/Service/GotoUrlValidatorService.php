<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

/**
 * Encapsulates functionality related to goto parameter validation.
 */
class GotoUrlValidatorService
{
    /**
     * @var array
     */
    protected $domainWhiteList;

    /**
     * @param array $domainWhiteList
     */
    public function __construct(array $domainWhiteList)
    {
        $this->domainWhiteList = $domainWhiteList;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function isValid($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $whiteListedDomains = $this->getDomainWhiteList();

        // Check if we match the domain exactly
        if (in_array($domain, $whiteListedDomains)) {
            return true;
        }

        foreach ($whiteListedDomains as $whiteListedDomain) {
            // Prevent things like 'mot.gov.uk.evilsitetime.com'
            $whiteListedDomain = '.'.trim($whiteListedDomain, '.');
            if (strpos($domain, $whiteListedDomain) === (strlen($domain) - strlen($whiteListedDomain))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getDomainWhiteList()
    {
        return $this->domainWhiteList;
    }
}
