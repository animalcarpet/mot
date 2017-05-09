<?php

namespace UserAdminTest\ViewModel;

use DvsaCommon\Validator\EmailAddressValidator;
use UserAdmin\Controller\UserSearchController;
use UserAdmin\ViewModel\UserSearchViewModel;

/**
 * Unit tests for UserSearchViewModel.
 */
class UserSearchViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserSearchViewModel
     */
    private $sut;

    public function setup()
    {
        $this->sut = new UserSearchViewModel(
            $this->buildUsers(),
            $this->buildCriteria()
        );
    }

    public function testDisplaySearchCriteria()
    {
        $criteria = 'Cheryl, Tunt, Stoke Gifford, L1 1PQ';
        $this->assertEquals($criteria, $this->sut->displaySearchCriteria());
    }

    public function testDisplayUserCount()
    {
        $this->assertEquals(4, $this->sut->getTotalResultNumber());
    }

    public function testIsAnythingFound()
    {
        $this->assertTrue($this->sut->isAnythingFound());
    }

    public function testGetSearchResult()
    {
        $this->assertEquals(4, count($this->sut->getSearchResult()));
    }

    public function testDisplayDobDaySearchCriteria()
    {
        $this->assertEquals('01', $this->sut->displayDobDaySearchCriteria());
    }

    public function testDisplayDobMonthSearchCriteria()
    {
        $this->assertEquals('01', $this->sut->displayDobMonthSearchCriteria());
    }

    public function testDisplayDobYearSearchCriteria()
    {
        $this->assertEquals('2015', $this->sut->displayDobYearSearchCriteria());
    }

    public function testHasSearchCriteria()
    {
        $this->assertTrue($this->sut->hasSearchCriteria());
    }

    /**
     * @dataProvider expandingAdditionalSearchCriteriaDataProvider
     */
    public function testExpadingAdditionalSearchCriteria(
        $firstName,
        $lastName,
        $email,
        $dobDay,
        $dobMonth,
        $dobYear,
        $town,
        $postcode,
        $result
    ) {
        $criteria = [
            UserSearchController::PARAM_FIRSTNAME => $firstName,
            UserSearchController::PARAM_LASTNAME => $lastName,
            UserSearchController::PARAM_EMAIL => $email,
            UserSearchController::PARAM_DOB_DAY => $dobDay,
            UserSearchController::PARAM_DOB_MONTH => $dobMonth,
            UserSearchController::PARAM_DOB_YEAR => $dobYear,
            UserSearchController::PARAM_TOWN => $town,
            UserSearchController::PARAM_POSTCODE => $postcode,
        ];

        $sut = new UserSearchViewModel(
            $this->buildUsers(),
            $criteria
        );

        $this->assertEquals($result, !empty($sut->getAdditionalSearchCriteria()));
    }

    public function expandingAdditionalSearchCriteriaDataProvider()
    {
        return [
            ['John', 'Smith', 'usersearchviewmodeltest@'.EmailAddressValidator::TEST_DOMAIN, null, null, null, null, null, true],
            ['John', 'Smith', null, 10, null, null, null, null, true],
            ['John', 'Smith', null, null, 10, null, null, null, true],
            ['John', 'Smith', null, null, null, 2015, null, null, true],
            ['John', 'Smith', null, null, null, null, 'Bristol', null, true],
            ['John', 'Smith', null, null, null, null, null, '1L 1PQ', true],
            ['John', 'Smith', null, null, null, null, null, null, false],
        ];
    }

    public function testDisplayDobSearchCriteriaNoBirth()
    {
        $criteria = $this->buildCriteria() + [UserSearchController::PARAM_DOB => '2015-01-01'];
        $this->sut = new UserSearchViewModel(
            $this->buildUsers(),
            $criteria
        );
        $this->assertEquals('1 January 2015', $this->sut->displayDobSearchCriteria());
    }

    private function buildCriteria()
    {
        return [
            'firstName' => 'Cheryl',
            'lastName' => 'Tunt',
            'middleName' => 'Thomas',
            'town' => 'Stoke Gifford',
            'postcode' => 'L1 1PQ',
            UserSearchController::PARAM_DOB_DAY => '01',
            UserSearchController::PARAM_DOB_MONTH => '01',
            UserSearchController::PARAM_DOB_YEAR => '2015',
        ];
    }

    private function buildUsers()
    {
        return [
            $this->buildPersonMock(),
            $this->buildPersonMock(),
            $this->buildPersonMock(),
            $this->buildPersonMock(),
        ];
    }

    private function buildPersonMock()
    {
        $stub = $this->getMockBuilder('DvsaCommon\Dto\Person\SearchPersonResultDto')
            ->disableOriginalConstructor()
            ->getMock();

        return $stub;
    }
}
