<?php

namespace OrganisationTest\ViewModel\View\Index;

use DvsaClient\Entity\Person;
use DvsaClient\Entity\VehicleTestingStation;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonContactDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Authorisation\AuthorisedExaminerViewAuthorisation;
use Organisation\Presenter\AuthorisedExaminerPresenter;
use Organisation\ViewModel\View\Index\EmployeeViewModel;
use Organisation\ViewModel\View\Index\IndexViewModel;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Class IndexViewModelTest.
 */
class IndexViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexViewModel
     */
    private $viewModel;

    /**
     * @var string
     */
    private $aedRole;

    /**
     * @var string
     */
    private $aedm;

    /**
     * @var Person
     */
    private $timmy;

    /**
     * @var Person
     */
    private $stevieweevie;

    /** @var AuthorisedExaminerViewAuthorisation */
    private $viewAuthorisation;

    /** @var Url */
    private $urlHelper;

    /** @var AuthorisedExaminerPresenter */
    private $presenter;

    public function setUp()
    {
        $this->viewAuthorisation = XMock::of(AuthorisedExaminerViewAuthorisation::class);
        $this->presenter = XMock::of(AuthorisedExaminerPresenter::class);
        $this->urlHelper = XMock::of(Url::class);
        $this->viewModel = new IndexViewModel($this->viewAuthorisation, new OrganisationDto(), $this->presenter, [], $this->createPositions(), [], $this->urlHelper);
    }

    private function createPositions()
    {
        $this->aedRole = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE;
        $this->aedm = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;

        $this->timmy = $this->createPerson(1);
        $this->stevieweevie = $this->createPerson(2);

        $janitorPosition = $this->createPosition($this->timmy, $this->aedRole);
        $pmPosition = $this->createPosition($this->stevieweevie, $this->aedm);
        $taPosition = $this->createPosition($this->stevieweevie, $this->aedRole);

        return [
            $janitorPosition,
            $pmPosition,
            $taPosition,
        ];
    }

    public function testPositionsAreGroupedByPerson()
    {
        // GIVEN
        $persons = [$this->stevieweevie, $this->timmy];
        $viewModel = $this->viewModel;

        // THEN
        $this->assertEquals(count($persons), $viewModel->getNumberOfEmployees());
    }

    public function testEmployeesAreIndexedByPersonId()
    {
        // GIVEN
        $stevieweevie = $this->stevieweevie;
        $employees = $this->viewModel->getEmployees();

        // THEN
        $this->assertSame($stevieweevie, $employees[$stevieweevie->getId()]->getPerson());
    }

    public function testMultiplePositionsPerPerson()
    {
        // GIVEN
        $stevieweevie = $this->stevieweevie;
        $employees = $this->viewModel->getEmployees();
        $stieviesRoles = [];
        $positions = $employees[$stevieweevie->getId()]->getPositions();
        foreach ($positions as $position) {
            $stieviesRoles[] = $position->getRole();
        }

        // THEN
        $this->assertContains($this->aedm, $stieviesRoles);
        $this->assertContains($this->aedRole, $stieviesRoles);
    }

    public function test_isLastPrincipal_shouldBeOk()
    {
        $john = $this->createPerson(1);
        $mike = $this->createPerson(2);

        $viewModel = new IndexViewModel($this->viewAuthorisation, new OrganisationDto(), $this->presenter, [], $this->createPositions(), [$john, $mike], $this->urlHelper);

        $this->assertTrue($viewModel->isLastPrincipal($mike));
        $this->assertFalse($viewModel->isLastPrincipal($john));
        $this->assertTrue($viewModel->hasPrincipals());
        $this->assertCount(2, $viewModel->getPrincipals());
    }

    public function test_isLastEmployee_shouldReturnFalse()
    {
        $viewModel = new IndexViewModel($this->viewAuthorisation, new OrganisationDto(), $this->presenter, [], $this->createPositions(), [], $this->urlHelper);

        $this->assertFalse($viewModel->isLastEmployee(new EmployeeViewModel(new PersonDto())));
    }

    public function test_getOrganisation_shouldBeOk()
    {
        $organisation = new OrganisationDto();
        $viewModel = new IndexViewModel($this->viewAuthorisation, $organisation, $this->presenter, [], $this->createPositions(), [], $this->urlHelper);

        $this->assertSame($organisation, $viewModel->getOrganisation());
    }

    public function test_vtsRelatedMethods_shouldReturnTrue()
    {
        $vts1 = new VehicleTestingStation();
        $vts2 = new VehicleTestingStation();
        $orgDto = new OrganisationDto();
        $viewModel1 = new IndexViewModel($this->viewAuthorisation, $orgDto, $this->presenter, [$vts1, $vts2], $this->createPositions(), [], $this->urlHelper);
        $this->assertFalse($viewModel1->isLastVts($vts1));
        $this->assertTrue($viewModel1->isLastVts($vts2));
        $this->assertTrue($viewModel1->shouldViewContactDetailsForVts());
        $this->assertCount($viewModel1->getNumberOfVehicleTestingStations(), $viewModel1->getVehicleTestingStations());

        $viewModel2 = new IndexViewModel($this->viewAuthorisation, $orgDto, $this->presenter, [], $this->createPositions(), [], $this->urlHelper);
        $this->assertFalse($viewModel2->shouldViewContactDetailsForVts());
    }

    public function test_getPrincipalPersonalContact_shouldReturnPersonalContactOnly()
    {
        $john = $this->createPerson(1, $this->createPersonContactDetails());
        $mike = $this->createPerson(2, $this->createPersonContactDetails());

        $viewModel = new IndexViewModel(
            $this->viewAuthorisation,
            new OrganisationDto(),
            $this->presenter,
            [],
            $this->createPositions(),
            [$john, $mike],
            $this->urlHelper
        );

        foreach ($viewModel->getPrincipals() as $index => $principal) {
            $aepContactDetails = $viewModel->getPrincipalContactDetails($index);

            $this->assertSame($principal->getContactDetails(), $aepContactDetails);
        }
    }

    /**
     * @param int          $id
     * @param ContactDto[] $contactDetails optional
     *
     * @return PersonDto
     */
    private function createPerson($id, $contactDetails = null)
    {
        $user = new PersonDto();
        $user->setId($id);

        if (is_array($contactDetails)) {
            $user->setContactDetails($contactDetails);
        }

        return $user;
    }

    private function createPosition($person, $role)
    {
        $position = new OrganisationPositionDto();
        $position->setPerson($person);
        $position->setRole($role);

        return $position;
    }

    /**
     * @return PersonContactDto[]
     */
    private function createPersonContactDetails()
    {
        $contactDetails = [];
        foreach ([
                     PersonContactType::PERSONAL,
                     PersonContactType::WORK,
                 ] as $type) {
            $contactDetails[] = $this->createPersonContactDetail($type);
        }

        return $contactDetails;
    }

    /**
     * @param string $contactType PersonContactType::PERSONAL | PersonContactType::WORK
     *
     * @return PersonContactDto
     */
    private function createPersonContactDetail($contactType)
    {
        $contactDetail = new PersonContactDto();
        $contactDetail->setType($contactType);

        return $contactDetail;
    }
}
