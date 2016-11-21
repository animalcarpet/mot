<?php

namespace DvsaMotTest\Controller;


use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\ReportUrlBuilder;
use DvsaMotTest\Service\CertificatePrintingService;
use Zend\Http\Response;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * All certificate printing related duties should be in here.
 */
class CertificatePrintingController extends AbstractDvsaMotTestController
{

    /**
     * @var CertificatePrintingService
     */
    protected $certificatePrintingService;

    /**
     * @param CertificatePrintingService $certificatePrintingService
     */
    public function __construct(CertificatePrintingService $certificatePrintingService)
    {
        $this->certificatePrintingService = $certificatePrintingService;
    }

    /**
     * Called to retrieve a PDF from the document service (and Jasper), and returns
     * the binary with content-type header.
     * Relies on an MOT ID being passed in the URL, which resolves to a document ID.
     *
     * @return Response
     * @throws RestApplicationException
     * @throws \Exception
     */
    public function retrievePdfAction()
    {
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
        $isDuplicate = $this->params('isDuplicate');

        $certificateUrl = ReportUrlBuilder::printCertificate($motTestNumber, ($isDuplicate ? 'dup' : null));

        $result = $this->getRestClient()->getPdf($certificateUrl); // @todo - add some pdf parsing checks in client

        $response = new Response;
        $response->setContent($result);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');
        return $response;
    }

}