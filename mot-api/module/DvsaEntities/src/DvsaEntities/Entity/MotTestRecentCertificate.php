<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * MotTestRecentCertificate
 *
 * @ORM\Table(
 *  name="mot_test_recent_certificate",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MotTestRecentCertificateRepository")
 */
class MotTestRecentCertificate
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="tester_person_id", type="integer", nullable=false)
     */
    protected $testerPersonId;

    /**
     * @var integer
     *
     * @ORM\Column(name="site_id", type="integer", nullable=false)
     */
    protected $vtsId;

    /**
     * @var integer
     *
     * @ORM\Column(name="mot_test_id", type="integer", nullable=false)
     */
    protected $motId;

    /**
     * @var integer
     *
     * @ORM\Column(name="prs_mot_test_id", type="integer", nullable=false)
     */
    protected $prsId;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=30, nullable=true)
     */
    protected $vin;

    /**
     * @var string
     *
     * @ORM\Column(name="registration", type="string", length=20, nullable=true)
     */
    protected $registration;

    /**
     * @var integer
     *
     * @ORM\Column(name="make_id", type="integer", nullable=true)
     */
    protected $makeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="model_id", type="integer", nullable=true)
     */
    protected $modelId;

    /**
     * @var integer
     *
     * @ORM\Column(name="model_detail_id", type="integer", nullable=true)
     */
    protected $modelDetailId;

    /**
     * @var string
     *
     * @ORM\Column(name="make_name", type="string", length=50, nullable=true)
     */
    protected $makeName;

    /**
     * @var integer
     *
     * @ORM\Column(name="model_name", type="string", length=50, nullable=true)
     */
    protected $modelName;

    /**
     * @var string
     *
     * @ORM\Column(name="model_detail_name", type="string", length=50, nullable=true)
     */
    protected $modelDetailName;

    /**
     * @var integer
     *
     * @ORM\Column(name="mot_test_status_id", type="integer", nullable=false)
     */
    protected $motTestStatusId;

    /**
     * @var integer
     *
     * @ORM\Column(name="generation_worker_id", type="integer", nullable=true)
     */
    protected $generationWorkerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="document_id", type="integer", nullable=false)
     */
    protected $documentId;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient_first_name", type="string", length=45, nullable=true)
     */
    protected $recipientFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient_family_name", type="string", length=45, nullable=true)
     */
    protected $recipientFamilyName;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient_email", type="string", length=255, nullable=true)
     */
    protected $recipientEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="certificate_status", type="string", length=20, nullable=true)
     */
    protected $certificateStatus;

    /**
     * @var DataTime
     *
     * @ORM\Column(name="generation_started_on", type="datetime", nullable=true)
     */
    protected $generationStartedOn;

    /**
     * @var DataTime
     *
     * @ORM\Column(name="generation_completed_on", type="datetime", nullable=true)
     */
    protected $generationCompletedOn;

    /**
     * @var string
     *
     * @ORM\Column(name="certificate_storage_key", type="string", length=50, nullable=true)
     */
    protected $certificateStorageKey;

    /**
     * @var string
     *
     * @ORM\Column(name="created_on", type="datetime", length=50, nullable=true)
     */
    protected $createdOn;

    /**
     * @var DataTime
     *
     * @ORM\Column(name="last_updated_on", type="datetime", nullable=true)
     */
    protected $lastUpdatedOn;

    /**
     * @ORM\OneToOne(targetEntity="MotTestStatus")
     * @ORM\JoinColumn(name="mot_test_status_id", referencedColumnName="id")
     **/
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="Make")
     * @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     **/
    protected $make;

    /**
     * @ORM\OneToOne(targetEntity="Model")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     **/
    protected $model;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getTesterPersonId()
    {
        return $this->testerPersonId;
    }

    /**
     * @param int $testerPersonId
     */
    public function setTesterPersonId($testerPersonId)
    {
        $this->testerPersonId = $testerPersonId;
    }

    /**
     * @return int
     */
    public function getVtsId()
    {
        return $this->vtsId;
    }

    /**
     * @param int $vtsId
     */
    public function setVtsId($vtsId)
    {
        $this->vtsId = $vtsId;
    }

    /**
     * @return int
     */
    public function getMotId()
    {
        return $this->motId;
    }

    /**
     * @param int $motId
     */
    public function setMotId($motId)
    {
        $this->motId = $motId;
    }

    /**
     * @return int
     */
    public function getPrsId()
    {
        return $this->prsId;
    }

    /**
     * @param int $prsId
     */
    public function setPrsId($prsId)
    {
        $this->prsId = $prsId;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param string $vin
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param string $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * @return int
     */
    public function getMakeId()
    {
        return $this->makeId;
    }

    /**
     * @param int $makeId
     */
    public function setMakeId($makeId)
    {
        $this->makeId = $makeId;
    }

    /**
     * @return int
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
    }

    /**
     * @return int
     */
    public function getModelDetailId()
    {
        return $this->modelDetailId;
    }

    /**
     * @param int $modelDetailId
     */
    public function setModelDetailId($modelDetailId)
    {
        $this->modelDetailId = $modelDetailId;
    }

    /**
     * @return string
     */
    public function getMakeName()
    {
        return $this->makeName;
    }

    /**
     * @param string $makeName
     */
    public function setMakeName($makeName)
    {
        $this->makeName = $makeName;
    }

    /**
     * @return int
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param int $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * @return string
     */
    public function getModelDetailName()
    {
        return $this->modelDetailName;
    }

    /**
     * @param string $modelDetailName
     */
    public function setModelDetailName($modelDetailName)
    {
        $this->modelDetailName = $modelDetailName;
    }

    /**
     * @return int
     */
    public function getMotTestStatusId()
    {
        return $this->motTestStatusId;
    }

    /**
     * @param int $motTestStatusId
     */
    public function setMotTestStatusId($motTestStatusId)
    {
        $this->motTestStatusId = $motTestStatusId;
    }

    /**
     * @return int
     */
    public function getGenerationWorkerId()
    {
        return $this->generationWorkerId;
    }

    /**
     * @param int $generationWorkerId
     */
    public function setGenerationWorkerId($generationWorkerId)
    {
        $this->generationWorkerId = $generationWorkerId;
    }

    /**
     * @return int
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param int $documentId
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * @return string
     */
    public function getRecipientFirstName()
    {
        return $this->recipientFirstName;
    }

    /**
     * @param string $recipientFirstName
     */
    public function setRecipientFirstName($recipientFirstName)
    {
        $this->recipientFirstName = $recipientFirstName;
    }

    /**
     * @return string
     */
    public function getRecipientFamilyName()
    {
        return $this->recipientFamilyName;
    }

    /**
     * @param string $recipientFamilyName
     */
    public function setRecipientFamilyName($recipientFamilyName)
    {
        $this->recipientFamilyName = $recipientFamilyName;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * @param string $recipientEmail
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * @return string
     */
    public function getCertificateStatus()
    {
        return $this->certificateStatus;
    }

    /**
     * @param string $certificateStatus
     */
    public function setCertificateStatus($certificateStatus)
    {
        $this->certificateStatus = $certificateStatus;
    }

    /**
     * @return DataTime
     */
    public function getGenerationStartedOn()
    {
        return $this->generationStartedOn;
    }

    /**
     * @param DataTime $generationStartedOn
     */
    public function setGenerationStartedOn($generationStartedOn)
    {
        $this->generationStartedOn = $generationStartedOn;
    }

    /**
     * @return DataTime
     */
    public function getGenerationCompletedOn()
    {
        return $this->generationCompletedOn;
    }

    /**
     * @param DataTime $generationCompletedOn
     */
    public function setGenerationCompletedOn(\DateTime $generationCompletedOn)
    {
        $this->generationCompletedOn = $generationCompletedOn;
    }

    /**
     * @return string
     */
    public function getCertificateStorageKey()
    {
        return $this->certificateStorageKey;
    }

    /**
     * @param string $certificateStorageKey
     */
    public function setCertificateStorageKey($certificateStorageKey)
    {
        $this->certificateStorageKey = $certificateStorageKey;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param mixed $make
     */
    public function setMake($make)
    {
        $this->make = $make;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}
