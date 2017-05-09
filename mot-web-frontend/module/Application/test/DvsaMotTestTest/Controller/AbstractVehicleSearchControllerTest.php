<?php

namespace DvsaMotTestTest\Controller;

use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaMotTest\Controller\VehicleSearchController;

/**
 * Class AbstractVehicleSearchControllerTest.
 */
abstract class AbstractVehicleSearchControllerTest extends AbstractDvsaMotTestTestCase
{
    const TEST_PARTIAL_VIN = '123321';
    const TEST_FULL_VIN = '12332112332112332';
    const TEST_REG = 'EL1 0FA';
    const TEST_FULL_VIN_WITH_SPACES = '12332 11233 21 123 32';

    const VEHICLE_ID = 9999;
    const VEHICLE_ID_ENC = '34eT4Q';    //  encrypted 9999

    protected function canBeAccessedForAuthenticatedRequest($action)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asVehicleExaminer());

        $this->getResponseForAction($action);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    protected function cantBeAccessedUnauthenticatedRequest($action)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction($action);
    }

    protected function requestSearch(array $params, $action = null, $method = 'post')
    {
        $queryParams = [];
        $postParams = [];
        if (strtolower($method) === 'get') {
            $queryParams = $params;
        } else {
            $postParams = $params;
        }

        return $this->getResultForAction2(
            $method, (!empty($action) ? $action : 'vehicle-search'), [], $queryParams, $postParams
        );
    }

    protected function assertFormIsValid($variables, $isFormValid = true)
    {
        /** @var \Zend\Form\Form $form */
        $form = $variables['form'];
        $this->assertEquals($form->isValid(), $isFormValid);
    }

    protected function getPositiveTestSearchResult()
    {
        return [
            'data' => [
                'resultType' => VehicleSearchController::SEARCH_RESULT_EXACT_MATCH,
                'vehicles' => [$this->getTestVehicleData()],
            ],
        ];
    }

    protected function getPositiveRetestSearchResult()
    {
        return [
            'data' => [
                'resultType' => VehicleSearchController::SEARCH_RESULT_EXACT_MATCH,
                'vehicle' => $this->getTestVehicleData(),
            ],
        ];
    }

    protected function getTestVehicleData()
    {
        return [
            'id' => self::VEHICLE_ID,
            'registration' => 'CRZ 4545',
            'vin' => 100000000001111111,
            'vehicle_class' => '4',
            'make' => 'FORD',
            'model' => 'FOCUS ZETEC',
            'year' => 2011,
            'colour' => 'SILVER',
            'fuel_type' => 'P',
            'isDvla' => false,
            'emptyVinReason' => null,
            'emptyRegistrationReason' => null,
            'isIncognito' => false,
        ];
    }
}
