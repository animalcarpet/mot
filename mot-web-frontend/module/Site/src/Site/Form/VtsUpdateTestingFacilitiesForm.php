<?php

namespace Site\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Zend\Stdlib\Parameters;

class VtsUpdateTestingFacilitiesForm extends AbstractFormModel
{
    const FIELD_TESTING_FACILITY_OPTL = 'facilityOptl';
    const FIELD_TESTING_FACILITY_TPTL = 'facilityTptl';

    const ERR_SITE_TYPE_REQUIRE = 'A location type must be selected';
    const ERR_TESTING_FACILITY_OPTL_REQUIRE = 'A number of OPTL must be selected';
    const ERR_TESTING_FACILITY_TPTL_REQUIRE = 'A number of TPTL must be selected';

    private $testingFacilityOptl = '';
    private $testingFacilityTptl = '';

    /** @var VehicleTestingStationDto $vtsDto */
    private $vtsDto;
    private $formUrl;

    public function fromPost(Parameters $data)
    {
        $this->clearEmptyParams($data);

        $this
            ->setTestingFacilityOptl($data->get(self::FIELD_TESTING_FACILITY_OPTL, ''))
            ->setTestingFacilityTptl($data->get(self::FIELD_TESTING_FACILITY_TPTL, ''));

        return $this;
    }

    public function toDto()
    {
        $facilities = [];

        for ($i = 0, $len = $this->getTestingFacilityOptl(); $i < $len; ++$i) {
            $facilities[] = (new FacilityDto())
                ->setType(
                    (new FacilityTypeDto())
                        ->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE)
                );
        }

        for ($i = 0, $len = $this->getTestingFacilityTptl(); $i < $len; ++$i) {
            $facilities[] = (new FacilityDto())
                ->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::TWO_PERSON_TEST_LANE));
        }

        $siteDto = new VehicleTestingStationDto();
        $siteDto->setFacilities($facilities);

        if ($this->getTestingFacilityTptl() !== '') {
            $siteDto->setIsTptlSelected(true);
        }
        if ($this->getTestingFacilityOptl() !== '') {
            $siteDto->setIsOptlSelected(true);
        }

        return $siteDto;
    }

    public function fromDto(VehicleTestingStationDto $vtsDto)
    {
        $this->vtsDto = $vtsDto;

        $this
            ->setTestingFacilityOptl($vtsDto->getOptlCount())
            ->setTestingFacilityTptl($vtsDto->getTptlCount());

        return $this;
    }

    public function addErrorsFromApi($errors)
    {
        $this->addErrors($errors);
    }

    /**
     * @return int
     */
    public function getTestingFacilityOptl()
    {
        return $this->testingFacilityOptl;
    }

    /**
     * @param int $testingFacilityOptl
     *
     * @return $this
     */
    public function setTestingFacilityOptl($testingFacilityOptl)
    {
        $this->testingFacilityOptl = $testingFacilityOptl;

        return $this;
    }

    /**
     * @return int
     */
    public function getTestingFacilityTptl()
    {
        return $this->testingFacilityTptl;
    }

    /**
     * @param int $testingFacilityTptl
     *
     * @return $this
     */
    public function setTestingFacilityTptl($testingFacilityTptl)
    {
        $this->testingFacilityTptl = $testingFacilityTptl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->formUrl;
    }

    /**
     * @param string $formUrl
     *
     * @return $this
     */
    public function setFormUrl($formUrl)
    {
        $this->formUrl = $formUrl;

        return $this;
    }

    public function getVtsDto()
    {
        return $this->vtsDto;
    }

    public function getTestingFacilities()
    {
        return [
            0, 1, 2, 3, 4, '5 or more',
        ];
    }
}
