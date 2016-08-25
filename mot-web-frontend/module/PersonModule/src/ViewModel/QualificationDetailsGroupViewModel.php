<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel;

use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\Frontend\PersonModule\Model\CertificateFields;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;

class QualificationDetailsGroupViewModel
{
    private $group;
    private $status;
    private $changeUrl;
    private $addUrl;
    /** @var MotTestingCertificateDto */
    private $motTestingCertificate;
    private $className;
    private $removeUrl;

    public function __construct($group, TesterGroupAuthorisationStatus $status, $motTestingCertificate,
        $changeUrl, $addUrl, $removeUrl)
    {
        $this->group = $group;
        $this->status = $status;
        $this->motTestingCertificate = $motTestingCertificate;
        $this->changeUrl = $changeUrl;
        $this->addUrl = $addUrl;
        $this->removeUrl = $removeUrl;
    }

    public function getTable($className)
    {
        $certificateFieldsData = (new CertificateFields())->getCertificateFields($this->status->getCode());
        $sidebarBadgeClass = $certificateFieldsData->getSidebarBadge()->getCssClass();

        if(!empty($this->motTestingCertificate)) {
            $certificateNumber = $this->motTestingCertificate->getCertificateNumber();
            $date = 'Awarded '.
                (new \DateTime($this->motTestingCertificate->getDateOfQualification()))
                ->format(DateTimeDisplayFormat::FORMAT_DATE);
        } else {
            if($this->status->getCode() == AuthorisationForTestingMotStatusCode::QUALIFIED) {
                $certificateNumber = 'Not needed';
                $date = 'Before 1 April 2016';
            } else {
                $certificateNumber = ' ';
                $date = null;
            }
        }

        $table = new GdsTable();
        $table->newRow('qualification-status-' . $className, $sidebarBadgeClass)->setLabel('Qualification status')
            ->setValue($this->status->getName());
        $certificateNumberRow = $table->newRow('certificate-number-' . $className)->setLabel('Certificate')
            ->setValue($certificateNumber);

        $certificateNumberRow
            ->setValueMetaData($date);

        if(!empty($this->motTestingCertificate)) {

            if(!empty($this->changeUrl)) {
                $certificateNumberRow->addActionLink('Change', $this->changeUrl, '', 'change');
            }

            if(!empty($this->removeUrl)) {
                $certificateNumberRow->addActionLink('Remove', $this->removeUrl, '', 'remove');
            }
        }

        if(!empty($this->addUrl)) {
            $certificateNumberRow->addActionLink('Add certificate and request a demo test', $this->addUrl);
        }

        return $table;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getChangeUrl()
    {
        return $this->changeUrl;
    }

    /**
     * @return mixed
     */
    public function getAddUrl()
    {
        return $this->addUrl;
    }

    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }
}