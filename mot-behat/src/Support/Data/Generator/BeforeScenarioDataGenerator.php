<?php

namespace Dvsa\Mot\Behat\Support\Data\Generator;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Utility\ArrayUtils;

class BeforeScenarioDataGenerator
{
    private $scope;
    private $authorisedExaminerData;
    private $siteData;
    private $userData;

    public function __construct(
        BeforeScenarioScope $scope,
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->scope = $scope;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    public function generate()
    {
        $tags = $this->scope->getScenario()->getTags();

        $this->generateNewAe($tags);
        $this->generateDefaultAe($tags);
        $this->generateNewSite($tags);
        $this->generateDefaultSite($tags);
        $this->generateNewUnassociatedSite($tags);
        $this->generateUser($tags);
        $this->generateTester($tags);
    }

    private function generateNewAe(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-ae") === 0) {
                preg_match("#create-ae\((.*?)\)#", $tag, $match);
                $this->generateAe($match[1]);
            }
        }
    }

    private function generateDefaultAe(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-default-ae") === 0) {
                preg_match("#create-default-ae\((.*?)\)#", $tag, $match);
                $ae = $this->generateAe($match[1]);
                $collection = SharedDataCollection::get(OrganisationDto::class);
                $collection->add($ae, AuthorisedExaminerData::DEFAULT_NAME);
            }
        }
    }

    private function generateAe($data)
    {
        $aeData = explode(",", $data);

        $aeName = ArrayUtils::get($aeData, 0);
        $aeName = $this->filter($aeName);
        $aeSlots = ArrayUtils::tryGet($aeData, 1, 1001);
        $aeSlots = (int)$this->filter($aeSlots);

        return $this->authorisedExaminerData->createWithCustomSlots($aeSlots, $aeName);
    }

    private function generateNewSite(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-site") === 0) {
                preg_match("#create-site\((.*?)\)#", $tag, $match);
                $this->generateSite($match[1]);
            }
        }
    }

    private function generateDefaultSite(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-default-site") === 0) {
                preg_match("#create-default-site\((.*?)\)#", $tag, $match);

                $site = $this->generateSite($match[1]);
                $collection = SharedDataCollection::get(SiteDto::class);
                $collection->add($site, SiteData::DEFAULT_NAME);

                $ae = $site->getOrganisation();
                $collection = SharedDataCollection::get(OrganisationDto::class);
                $collection->add($ae, AuthorisedExaminerData::DEFAULT_NAME);
            }
        }
    }

    private function generateNewUnassociatedSite(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-unassociated-site") === 0) {
                preg_match("#create-unassociated-site\((.*?)\)#", $tag, $match);
                $siteName = explode(",", $match[1]);
                $this->siteData->createUnassociatedSite([SiteParams::NAME => $siteName]);
            }
        }
    }

    private function generateSite($data)
    {
        $aeData = explode(",", $data);

        $siteName = ArrayUtils::get($aeData, 0);
        $siteName = $this->filter($siteName);
        $aeName = ArrayUtils::tryGet($aeData, 1, AuthorisedExaminerData::DEFAULT_NAME);
        $aeName = $this->filter($aeName);

        return $this->siteData->createWithParams([SiteParams::NAME => $siteName, AuthorisedExaminerParams::AE_NAME => $aeName]);
    }

    private function generateUser(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-user") === 0) {
                preg_match("#create-user\((.*?)\)#", $tag, $match);
                $name = $this->filter($match[1]);

                $this->userData->createUser($name);
            }
        }
    }

    private function generateTester(array $tags)
    {
        foreach ($tags as $tag) {
            if (strpos($tag, "create-tester") === 0) {
                preg_match("#create-tester\((.*?)\)#", $tag, $match);
                $name = $this->filter($match[1]);

                $this->userData->createTester($name);
            }
        }
    }

    private function filter($string)
    {
        return trim(str_replace("\"", "", $string));
    }
}
