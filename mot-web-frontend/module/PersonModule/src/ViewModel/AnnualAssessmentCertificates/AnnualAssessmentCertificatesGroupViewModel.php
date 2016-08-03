<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;

use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Search\SearchParamsDto;
use Organisation\Presenter\UrlPresenterData;
use Report\Table\Formatter\UrlPresenterLinkWithParams;
use Report\Table\Table;

class AnnualAssessmentCertificatesGroupViewModel
{
    const NUMERIC_CLASS = 'numeric';
    const CERTIFICATE_NUMBER = 'Certificate number';
    const DATE_AWARDED = 'Date awarded';
    const SCORE_ACHIEVED = 'Score achieved';
    const ACTION_LINKS = "actions";

    /** @var  MotTestingAnnualCertificateDto[] $annualAssessmentCertificates */
    private $annualAssessmentCertificates;
    /** @var FormContext */
    private $context;
    /** @var AnnualAssessmentCertificatesRoutes */
    private $routes;
    private $group;
    /** @var  AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    /**
     * @param MotTestingAnnualCertificateDto[] $annualAssessmentCertificates
     */
    public function __construct(
        array $annualAssessmentCertificates,
        FormContext $context,
        AnnualAssessmentCertificatesRoutes $routes,
        $group,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions
    ) {
        $this->annualAssessmentCertificates = $annualAssessmentCertificates;
        $this->context = $context;
        $this->routes = $routes;
        $this->group = $group;
        $this->certificatesPermissions = $certificatesPermissions;
    }

    public function isTableEmpty()
    {
        return empty($this->annualAssessmentCertificates);
    }

    public function getTable()
    {
        $canUpdate = $this->certificatesPermissions->isGrantedUpdate(
            $this->context->getTargetPersonId(),
            $this->context->getLoggedInPersonId()
        );
        $canRemove = $this->certificatesPermissions->isGrantedRemove(
            $this->context->getTargetPersonId(),
            $this->context->getLoggedInPersonId()
        );

        $table = new Table();
        $rows = [];

        foreach ($this->annualAssessmentCertificates AS $annualAssessmentCertificate) {
            $actionLinks = [];

            $params = $this->context->getController()->params()->fromRoute() + [
                    'group' => $this->group,
                    'certificateId' => $annualAssessmentCertificate->getId()
                ];

            if ($canUpdate) {
                $url = new UrlPresenterData(
                    "Change",
                    $this->routes->getEditRoute(),
                    $params,
                    [],
                    "change-" . $annualAssessmentCertificate->getId()
                );

                $actionLinks[] = $url;
            }

            if ($canRemove) {
                $url = new UrlPresenterData(
                    "Remove",
                    $this->routes->getRemove(),
                    $params,
                    [],
                    "remove-" . $annualAssessmentCertificate->getId()
                );

                $actionLinks[] = $url;
            }

            $rows[] =
                [
                    self::CERTIFICATE_NUMBER => $annualAssessmentCertificate->getCertificateNumber(),
                    self::DATE_AWARDED => DateTimeDisplayFormat::date($annualAssessmentCertificate->getExamDate()),
                    self::SCORE_ACHIEVED => $annualAssessmentCertificate->getScore() . '%',
                    self::ACTION_LINKS => $actionLinks,
                    self::DATE_AWARDED => DateTimeDisplayFormat::date($annualAssessmentCertificate->getExamDate()),
                    self::SCORE_ACHIEVED => $annualAssessmentCertificate->getScore() . '%',
                    self::ACTION_LINKS => $actionLinks
                ];
        }

        $table->setData($rows)
            ->setColumns($this->getTableColumns())
            ->setSearchParams(new SearchParamsDto());

        $table->getTableOptions()->setTableId('certificate-table-group-' . $this->group);

        return $table;
    }

    /**
     * @return array
     */
    private function getTableColumns()
    {
        return [
            [
                'title' => self::CERTIFICATE_NUMBER,
                'sub' => [
                    [
                        'field' => self::CERTIFICATE_NUMBER,
                    ],
                ],
            ],
            [
                'title' => self::DATE_AWARDED,
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::DATE_AWARDED,
                    ],
                ],
            ],
            [
                'title' => self::SCORE_ACHIEVED,
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::SCORE_ACHIEVED,
                    ],
                ],
            ],
            [
                'title' => '',
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::ACTION_LINKS,
                        'formatter' => UrlPresenterLinkWithParams::class,
                    ],
                ],
            ],
        ];
    }
}