<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ApplicationForAuthorisationTestingMot.
 *
 * @ORM\Table(name="app_for_auth_testing_mot", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_app_for_auth_testing_mot_2_idx", columns={"auth_for_testing_mot_id"}), @ORM\Index(name="fk_app_for_auth_testing_mot_3_idx", columns={"status_id"}), @ORM\Index(name="fk_app_for_auth_testing_mot_4_idx", columns={"created_by"}), @ORM\Index(name="fk_app_for_auth_testing_mot_5_idx", columns={"last_updated_by"}), @ORM\Index(name="fk_app_for_auth_testing_mot_1_idx", columns={"application_id"})})
 * @ORM\Entity
 */
class ApplicationForAuthorisationTestingMot extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Application
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Application")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     * })
     */
    private $application;

    /**
     * @var \DvsaEntities\Entity\AuthorisationForTestingMot
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthorisationForTestingMot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="auth_for_testing_mot_id", referencedColumnName="id")
     * })
     */
    private $authorisationForTestingMot;

    /**
     * @var \DvsaEntities\Entity\AuthorisationForTestingMotStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthorisationForTestingMotStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * Set application.
     *
     * @param \DvsaEntities\Entity\Application $application
     *
     * @return ApplicationForAuthorisationTestingMot
     */
    public function setApplication(\DvsaEntities\Entity\Application $application = null)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application.
     *
     * @return \DvsaEntities\Entity\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set authorisationForTestingMot.
     *
     * @param \DvsaEntities\Entity\AuthorisationForTestingMot $authorisationForTestingMot
     *
     * @return ApplicationForAuthorisationTestingMot
     */
    public function setAuthorisationForTestingMot(
        \DvsaEntities\Entity\AuthorisationForTestingMot $authorisationForTestingMot = null
    ) {
        $this->authorisationForTestingMot = $authorisationForTestingMot;

        return $this;
    }

    /**
     * Get authorisationForTestingMot.
     *
     * @return \DvsaEntities\Entity\AuthorisationForTestingMot
     */
    public function getAuthorisationForTestingMot()
    {
        return $this->authorisationForTestingMot;
    }

    /**
     * @param AuthorisationForTestingMotStatus $status
     *
     * @return $this
     */
    public function setStatus(AuthorisationForTestingMotStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return AuthorisationForTestingMotStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
