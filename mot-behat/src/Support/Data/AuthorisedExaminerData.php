<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\SiteDto;

class AuthorisedExaminerData
{
    const DEFAULT_NAME = "default";

    private $userData;
    private $authorisedExaminer;
    private $testSupportHelper;

    private $aeCollection;

    public function __construct(
        UserData $userData,
        AuthorisedExaminer $authorisedExaminer,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->userData = $userData;
        $this->authorisedExaminer = $authorisedExaminer;
        $this->testSupportHelper = $testSupportHelper;
        $this->aeCollection = SharedDataCollection::get(OrganisationDto::class);
    }

    public function create($slots = 1001, $name = self::DEFAULT_NAME)
    {
        $ae = $this->tryGet($name);
        if ($ae !== null) {
            return $ae;
        }

        $ae = $this->createWithoutAedm($slots, $name);
        $this->userData->createAedm(["aeIds" => [$ae->getId()]]);

        return $ae;
    }

    /**
     * @param int $slots
     * @param string $name
     * @return OrganisationDto
     */
    public function createWithoutAedm($slots = 1001, $name = self::DEFAULT_NAME)
    {
        $ae = $this->tryGet($name);
        if ($ae !== null) {
            return $ae;
        }

        $ao1User = $this->userData->createAreaOffice1User();

        return $this->createByUser($ao1User, $name, $slots);
    }

    public function createByUser(AuthenticatedUser $user, $aeName = self::DEFAULT_NAME, $slots = 1001)
    {
        $data = [
            "slots" => $slots,
            "requestor" => [
                "username" => $user->getUsername(),
                "password" => "Password1"
            ]
        ];

        $ae = $this->testSupportHelper->getAeService()->create($data);

        $aea = new AuthorisedExaminerAuthorisationDto();
        $aea->setAuthorisedExaminerRef($ae->data["aeRef"]);

        $dto = new OrganisationDto();
        $dto
            ->setId($ae->data["id"])
            ->setName($ae->data["aeName"])
            ->setAuthorisedExaminerAuthorisation($aea);

        $this->aeCollection->add($dto, $aeName);

        return $dto;
    }

    public function linkAuthorisedExaminerWithSite(OrganisationDto $ae, SiteDto $site)
    {
        $ao1User = $this->userData->createAreaOffice1User();
        $this->authorisedExaminer->linkAuthorisedExaminerWithSite(
            $ao1User->getAccessToken(),
            $ae->getId(),
            $site->getSiteNumber()
        );
    }

    public function unlinkAuthorisedExaminerWithSite(OrganisationDto $ae, SiteDto $site)
    {
        $linkId = $this->testSupportHelper->getAeService()->getLinkId($ae->getId(), $site->getId());
        $ao1User = $this->userData->createAreaOffice1User();

        return $this->authorisedExaminer->unlinkSiteFromAuthorisedExaminer($ao1User->getAccessToken(), $ae->getId(), $linkId);
    }

    public function get($aeName = self::DEFAULT_NAME)
    {
        $ae = $this->tryGet($aeName);
        if ($ae === null) {
            throw new \InvalidArgumentException(sprintf("Authorised Examiner with name '%s' not found", $aeName));
        }

        return $ae;
    }

    /**
     * @param string $aeName
     * @return OrganisationDto|null
     */
    public function tryGet($aeName = self::DEFAULT_NAME)
    {
        if ($this->aeCollection->containsKey($aeName)) {
            return $this->aeCollection->get($aeName);
        }

        $aes = $this->aeCollection->filter(function (OrganisationDto $ae) use ($aeName) {
            return $ae->getName() === $aeName;
        });

        if (count($aes) === 1) {
            return $aes->first();
        }

        return null;
    }
}
