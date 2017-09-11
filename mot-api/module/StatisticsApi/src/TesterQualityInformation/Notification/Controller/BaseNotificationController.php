<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\TqiNotificationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;

abstract class BaseNotificationController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    /**
     * @var TqiNotificationService
     */
    protected $service;
    /**
     * @var MotAuthorisationServiceInterface
     */
    protected $authorisationService;

    public function create()
    {
        try {
            $this->service->execute();
        } catch (\LogicException $exception) {
            $this->response->setStatusCode(Response::STATUS_CODE_412);
            return $this->getExceptionResponse($exception);
        } catch (\Throwable $exception) {
            $this->response->setStatusCode(Response::STATUS_CODE_500);
            return $this->getExceptionResponse($exception);
        }

        return ApiResponse::jsonOk();
    }

    protected function getExceptionResponse(\Throwable $exception)
    {
        $this->getLogger()->err($exception->getMessage(), ['ex' => $exception]);
        return new JsonModel([
            'errors' => $exception->getMessage(),
        ]);
    }

    public function checkIfNotificationHasBeenSent(string $permission, int $notificationTemplateId)
    {
        $this->authorisationService->assertGranted($permission);

        $response = $this->service->checkIfNotificationHasBeenSent($notificationTemplateId);

        return ApiResponse::jsonOk($response);
    }
}