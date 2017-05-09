<?php

namespace UserAdminTest\Presenter;

use DvsaCommon\Dto\Person\SearchPersonResultDto;
use UserAdmin\Presenter\PersonPresenter;

/**
 * Unit tests for PersonPresenter.
 */
class PersonPresenterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersonPresenter
     */
    private $sut;

    public function setup()
    {
        $this->sut = new PersonPresenter(
            $this->buildSearchPersonResultDto()
        );
    }

    public function testCanDisplayName()
    {
        $name = 'Mallory Thomas Archer';

        $this->assertEquals($name, $this->sut->displayFullName());
    }

    public function testCanDisplayUsername()
    {
        $name = 'tester1';

        $this->assertEquals($name, $this->sut->displayUsername());
    }

    public function testCanDisplayDateOfBirth()
    {
        $dob = '24 April 1981';

        $this->assertEquals($dob, $this->sut->displayUserDateOfBirth());
    }

    public function testCanDisplayAddress()
    {
        $address = 'Straw Hut, Liverpool';

        $this->assertEquals($address, $this->sut->displayUserAddress());
    }

    public function testEmptyAddressDataDisplaysAnEmptyString()
    {
        $address = '';
        $sp = new PersonPresenter($this->buildSearchPersonResultDtoWithEmptyAddressDetails());

        $this->assertEquals($address, $sp->displayUserAddress());
    }

    public function testCanCreateDecoratedList()
    {
        $persons = [
            $this->buildSearchPersonResultDto(),
            $this->buildSearchPersonResultDto(),
            $this->buildSearchPersonResultDto(),
            $this->buildSearchPersonResultDto(),
        ];

        $decorated = PersonPresenter::decorateList($persons);
        $this->assertEquals(count($persons), count($decorated));
        $this->assertInstanceOf(PersonPresenter::class, array_pop($decorated));
    }

    public function testDisplayPostcode()
    {
        $postcode = 'BS1 6JZ';
        $this->assertEquals($postcode, $this->sut->displayPostcode());
    }

    public function testGetPersonId()
    {
        $personId = 1;
        $this->assertEquals($personId, $this->sut->getPersonId());
    }

    /**
     * @return \DvsaCommon\Dto\Person\SearchPersonResultDto
     */
    private function buildSearchPersonResultDto()
    {
        return new SearchPersonResultDto(
            [
                'id' => '1',
                'firstName' => 'Mallory',
                'lastName' => 'Archer',
                'middleName' => 'Thomas',
                'dateOfBirth' => '1981-04-24',
                'addressLine1' => 'Straw Hut',
                'addressLine2' => 'not relevant',
                'addressLine3' => 'not relevant',
                'addressLine4' => 'not relevant',
                'town' => 'Liverpool',
                'postcode' => 'BS1 6JZ',
                'username' => 'tester1',
            ]
        );
    }

    /**
     * @return \DvsaCommon\Dto\Person\SearchPersonResultDto
     */
    private function buildSearchPersonResultDtoWithEmptyAddressDetails()
    {
        return new SearchPersonResultDto(
            [
                'id' => '1',
                'firstName' => 'Mallory',
                'lastName' => 'Archer',
                'middleName' => 'Thomas',
                'dateOfBirth' => '1981-04-24',
                'addressLine1' => null,
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => null,
                'postcode' => null,
                'username' => 'tester1',
            ]
        );
    }
}
