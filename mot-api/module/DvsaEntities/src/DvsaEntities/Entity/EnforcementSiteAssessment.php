<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiteContact.
 *
 * @ORM\Table(name="enforcement_site_assessment")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteRiskAssessmentRepository")
 */
class EnforcementSiteAssessment extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="ae_organisation_id", type="integer", nullable=false)
     */
    protected $aeOrganisationId;

    /**
     * @ORM\Column(name="site_assessment_score", type="decimal", precision=2, scale=2, nullable=true)
     */
    protected $siteAssessmentScore;

    /**
     * @ORM\Column(name="ae_representative_name", type="string", nullable=true)
     */
    protected $aeRepresentativeName;

    /**
     * @ORM\Column(name="ae_representative_position", type="string", nullable=false)
     */
    protected $aeRepresentativePosition;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tester_person_id", referencedColumnName="id")
     * })
     */
    protected $tester;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examiner_person_id", referencedColumnName="id")
     * })
     */
    protected $examiner;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ae_representative_person_id", referencedColumnName="id")
     * })
     */
    protected $representative;

    /**
     * @ORM\Column(name="visit_date", type="datetime", nullable=false)
     */
    protected $visitDate;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", fetch="LAZY", inversedBy="siteRiskAssessments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeOrganisationId()
    {
        return $this->aeOrganisationId;
    }

    /**
     * @param mixed $aeOrganisationId
     *
     * @return $this
     */
    public function setAeOrganisationId($aeOrganisationId)
    {
        $this->aeOrganisationId = $aeOrganisationId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiteAssessmentScore()
    {
        return $this->siteAssessmentScore;
    }

    /**
     * @param mixed $siteAssessmentScore
     *
     * @return $this
     */
    public function setSiteAssessmentScore($siteAssessmentScore)
    {
        $this->siteAssessmentScore = $siteAssessmentScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeRepresentativeName()
    {
        return $this->aeRepresentativeName;
    }

    /**
     * @param mixed $aeRepresentativeName
     *
     * @return $this
     */
    public function setAeRepresentativeName($aeRepresentativeName)
    {
        $this->aeRepresentativeName = $aeRepresentativeName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeRepresentativePosition()
    {
        return $this->aeRepresentativePosition;
    }

    /**
     * @param mixed $aeRepresentativePosition
     *
     * @return $this
     */
    public function setAeRepresentativePosition($aeRepresentativePosition)
    {
        $this->aeRepresentativePosition = $aeRepresentativePosition;

        return $this;
    }

    /**
     * @return Person
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * @param Person $tester
     *
     * @return $this
     */
    public function setTester($tester)
    {
        $this->tester = $tester;

        return $this;
    }

    /**
     * @return Person
     */
    public function getExaminer()
    {
        return $this->examiner;
    }

    /**
     * @param Person $examiner
     *
     * @return $this
     */
    public function setExaminer($examiner)
    {
        $this->examiner = $examiner;

        return $this;
    }

    /**
     * @return Person
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    /**
     * @param Person $representative
     *
     * @return $this
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }

    /**
     * @param mixed $visitDate
     *
     * @return $this
     */
    public function setVisitDate($visitDate)
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     *
     * @return $this
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }
}
