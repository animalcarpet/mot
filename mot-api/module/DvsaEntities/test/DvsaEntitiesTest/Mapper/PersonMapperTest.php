<?php

namespace DvsaEntityTest\Mapper;

use DvsaCommon\Date\DateUtils;
use DvsaEntities\Mapper\PersonMapper;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Title;
use DvsaEntities\Repository\GenderRepository;
use DvsaEntities\Repository\TitleRepository;

/**
 * tests for PersonMapper.
 */
class PersonMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var PersonMapper */
    private $personMapper;
    private $stubTitle;
    private $stubGender;

    public function setup()
    {
        $titleRepository = $this->getMockTitleRepository();
        $genderRepository = $this->getMockGenderRepository();

        $this->personMapper = new PersonMapper($titleRepository, $genderRepository);
    }

    public function testMapToObjectSetsDefaultValues()
    {
        $testData = [];
        $expected = (new Person())
            ->setFirstName('')
            ->setMiddleName('')
            ->setFamilyName('');

        $actual = $this->personMapper->mapToObject(new Person(), $testData);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToObject()
    {
        $testData = [
            'firstName' => 'Harold',
            'middleName' => 'Jay',
            'surname' => 'Harrisson',
            'title' => 'Mr',
            'gender' => 'Male',
            'dateOfBirth' => '1992-04-01',
        ];
        $expected = (new Person())
            ->setFirstName('Harold')
            ->setMiddleName('Jay')
            ->setFamilyName('Harrisson')
            ->setTitle($this->stubTitle)
            ->setGender($this->stubGender)
            ->setDateOfBirth(DateUtils::toDate('1992-04-01'));

        $actual = $this->personMapper->mapToObject(new Person(), $testData);

        $this->assertEquals($expected, $actual);
    }

    private function getMockTitleRepository()
    {
        $this->stubTitle = new Title();

        $titleRepository = \DvsaCommonTest\TestUtils\XMock::of(TitleRepository::class, ['getByName']);

        $titleRepository
            ->expects($this->any())
            ->method('getByName')
            ->will($this->returnValue($this->stubTitle));

        return $titleRepository;
    }

    private function getMockGenderRepository()
    {
        $this->stubGender = new Gender();

        $genderRepository = \DvsaCommonTest\TestUtils\XMock::of(GenderRepository::class, ['getByName']);

        $genderRepository
            ->expects($this->any())
            ->method('getByName')
            ->will($this->returnValue($this->stubGender));

        return $genderRepository;
    }
}
