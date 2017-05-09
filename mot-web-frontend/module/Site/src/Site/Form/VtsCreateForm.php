<?php

namespace Site\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaClient\ViewModel\ContactDetailFormModel;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\CountryCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\SiteTypeName;
use Zend\Stdlib\Parameters;

class VtsCreateForm extends AbstractFormModel
{
    const FIELD_TESTING_FACILITY_OPTL = 'facilityOptl';
    const FIELD_TESTING_FACILITY_TPTL = 'facilityTptl';
    const FIELD_VEHICLE_CLASS = 'classes';
    const FIELD_SITE_TYPE = 'location';
    const FIELD_COUNTRY = 'country';
    const FIELD_NAME = 'name';

    const ERR_SITE_TYPE_REQUIRE = 'A location type must be selected';
    const ERR_TESTING_FACILITY_OPTL_REQUIRE = 'A number of OPTL must be selected';
    const ERR_TESTING_FACILITY_TPTL_REQUIRE = 'A number of TPTL must be selected';
    const ERR_VEHICLE_CLASS_REQUIRE = '1 or more vehicle classes must be selected';

    private $name;
    private $classes;
    private $type;
    private $testingFacilityOptl = '';
    private $testingFacilityTptl = '';
    private $country = CountryCode::ENGLAND;

    /**
     * @var ContactDetailFormModel
     */
    protected $contactModel;

    private $formUrl;

    public function __construct()
    {
        $this->contactModel = new ContactDetailFormModel(SiteContactTypeCode::BUSINESS);
    }

    public function fromPost(Parameters $data)
    {
        $this->clearEmptyParams($data);

        $this
            ->setName($data->get(self::FIELD_NAME))
            ->setClasses($data->get(self::FIELD_VEHICLE_CLASS))
            ->setType($data->get(self::FIELD_SITE_TYPE))
            ->setCountry($data->get(self::FIELD_COUNTRY, CountryCode::ENGLAND))
            ->setTestingFacilityOptl($data->get(self::FIELD_TESTING_FACILITY_OPTL, ''))
            ->setTestingFacilityTptl($data->get(self::FIELD_TESTING_FACILITY_TPTL, ''));

        $this->contactModel->fromPost($data);

        return $this;
    }

    public function toDto()
    {
        //  logical block :: fill contacts
        $contact = $this->contactModel->toDto(new SiteContactDto());

        //  logical block :: fill facilities
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

        //  logical block :: assemble dto
        $siteDto = new VehicleTestingStationDto();
        $siteDto
            ->setName($this->getName())
            ->setType($this->getType())
            ->setTestClasses($this->getClasses())
            ->setFacilities($facilities)
            ->setIsScottishBankHoliday($this->getCountry() == CountryCode::SCOTLAND)
            ->setIsDualLanguage($this->getCountry() == CountryCode::WALES)
            ->setContacts([$contact]);

        if ($this->getTestingFacilityTptl() !== '') {
            $siteDto->setIsTptlSelected(true);
        }
        if ($this->getTestingFacilityOptl() !== '') {
            $siteDto->setIsOptlSelected(true);
        }

        return $siteDto;
    }

    public function addErrorsFromApi($errors)
    {
        $this->addErrors($errors);
        $this->contactModel->addErrorsFromApi($errors);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     *
     * @return $this
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;

        return $this;
    }

    /**
     * @param int $class
     *
     * @return bool
     */
    public function isClassChecked($class)
    {
        if (!empty($this->classes)) {
            return in_array($class, $this->classes);
        }

        return false;
    }

    public function getTestingFacilities()
    {
        return [
            0, 1, 2, 3, 4, '5 or more',
        ];
    }

    public function getSiteTypes()
    {
        return [
            SiteTypeCode::VEHICLE_TESTING_STATION => SiteTypeName::VEHICLE_TESTING_STATION,
            SiteTypeCode::AREA_OFFICE => SiteTypeName::AREA_OFFICE,
            SiteTypeCode::TRAINING_CENTRE => SiteTypeName::TRAINING_CENTRE,
        ];
    }

    public function getCountries()
    {
        return [
            CountryCode::ENGLAND => 'England',
            CountryCode::SCOTLAND => 'Scotland',
            CountryCode::WALES => 'Wales',
        ];
    }

    /**
     * @return ContactDetailFormModel
     */
    public function getContactModel()
    {
        return $this->contactModel;
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
}
