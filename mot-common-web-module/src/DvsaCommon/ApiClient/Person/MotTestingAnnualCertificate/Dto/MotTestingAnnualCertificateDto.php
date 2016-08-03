<?php

namespace DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class MotTestingAnnualCertificateDto implements ReflectiveDtoInterface
{
    private $id;
    private $certificateNumber;
    private $examDate;
    private $score;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MotTestingAnnualCertificateDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificateNumber()
    {
        return $this->certificateNumber;
    }

    /**
     * @param string $certificateNumber
     * @return MotTestingAnnualCertificateDto
     */
    public function setCertificateNumber($certificateNumber)
    {
        $this->certificateNumber = $certificateNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExamDate()
    {
        return $this->examDate;
    }

    /**
     * @param \DateTime $examDate
     * @return MotTestingAnnualCertificateDto
     */
    public function setExamDate($examDate)
    {
        $this->examDate = $examDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return MotTestingAnnualCertificateDto
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }
}