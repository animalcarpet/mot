<?php

namespace Dvsa\Mot\Behat\Support\Data\Generator;

use Dvsa\Mot\Behat\Support\Api\DataCatalog;
use Dvsa\Mot\Behat\Support\Api\VehicleDictionary;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAedm;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAreaOffice;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultMake;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultModel;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultVehicleTestingStation;
use Dvsa\Mot\Behat\Support\Data\Map\MakeMap;
use Dvsa\Mot\Behat\Support\Data\Model\Catalog;
use Dvsa\Mot\Behat\Support\Data\Model\VehicleMakeDictionary;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Dto\Vehicle\ModelDto;
use TestSupport\Service\AccountService;

class DefaultDataGenerator
{
    private $authorisedExaminerData;
    private $siteData;
    private $userData;
    private $session;
    private $dataCatalog;
    private $vehicleDictionary;

    public function __construct(
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        UserData $userData,
        Session $session,
        DataCatalog $dataCatalog,
        VehicleDictionary $vehicleDictionary
    )
    {
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->session = $session;
        $this->dataCatalog = $dataCatalog;
        $this->vehicleDictionary = $vehicleDictionary;
    }

    public function generate()
    {
        $site = DefaultVehicleTestingStation::get();
        $aedm = DefaultAedm::get();

        if ($site === null) {
            $siteAreaOffice = $this->siteData->createAreaOffice();
            DefaultAreaOffice::set($siteAreaOffice);

            $site = $this->siteData->create();
            DefaultVehicleTestingStation::set($site);

            $organisation = $site->getOrganisation();
            DefaultAuthorisedExaminer::set($organisation);

            $aedm = $this->userData->getAedmByAeId($organisation->getId());
            DefaultAedm::set($aedm);

            $dataCatalog = $this->dataCatalog->getData($aedm->getAccessToken());
            Catalog::set($dataCatalog->getBody()->getData());

            $makeList = $this->vehicleDictionary->getMakeList($aedm->getAccessToken());
            VehicleMakeDictionary::set($makeList->getBody()->getData());
            $makeMap = new MakeMap();
            $bmw = $makeMap->getByCode("18811");
            DefaultMake::set($bmw);

            $modelResponse = $this->vehicleDictionary->getModelListByMakeId($aedm->getAccessToken(), $bmw->getId());
            $modelList = $modelResponse->getBody()->getData();
            foreach ($modelList as $model) {
                if ($model["code"] === "01459") {

                    $dto = new ModelDto();
                    $dto->setId($model["id"]);
                    $dto->setName($model["name"]);
                    $dto->setCode($model["code"]);

                    DefaultModel::set($dto);
                    break;
                }
            }

        } else {
            $this->siteData->getAll()->add($site, SiteData::DEFAULT_NAME);
            $organisation = $site->getOrganisation();
            $this->authorisedExaminerData->getAll()->add($organisation, AuthorisedExaminerData::DEFAULT_NAME);

            $aedm = $this->session->startSession($aedm->getUsername(), AccountService::PASSWORD);
            $this->userData->getAll()->add($aedm, sprintf("aedm_%d", $organisation->getId()));
        }
    }
}
