package uk.gov.dvsa.ui.pages.profile;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.ChangeEmailDetailsPage;
import uk.gov.dvsa.ui.pages.ChangeTelephoneDetailsPage;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.changedriverlicence.ChangeDrivingLicencePage;
import uk.gov.dvsa.ui.pages.dvsa.ManageRolesPage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsPage;

public abstract class ProfilePage extends Page {

    public ProfilePage(MotAppDriver driver) {
        super(driver);
    }

    public boolean drivingLicenceIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean addEditDrivingLicenceLinkExists() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ManageRolesPage clickManageRolesLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public RolesAndAssociationsPage clickRolesAndAssociationsLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public String getDrivingLicenceForPerson() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ChangeDrivingLicencePage clickChangeDrivingLicenceLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ChangeNamePage clickChangeNameLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ChangeDateOfBirthPage clickChangeDOBLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ChangeTelephoneDetailsPage clickChangeTelephoneLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public ChangeEmailDetailsPage clickChangeEmailLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public String getMessageSuccess() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isSuccessMessageDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isTesterQualificationStatusDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isDrivingLicenceInformationIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isChangeDrivingLicenceLinkIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isChangeDOBLinkIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isRolesAndAssociationsLinkDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isChangeEmailLinkIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isDvsaRolesSectionIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isQualificationStatusSectionIsDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isAccountSecuritySectionDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isAccountManagementSectionDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isChangeQualificationLinksDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isChangeNameLinkDisplayed() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString() );
    }

    public boolean isPageLoaded() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString());
    }

    public ChangeAddressPage clickChangeAddressLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString());
    }

    public UserSearchResultsPage clickCancelAndReturnToSearchResults() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString());
    }

    public QualificationDetailsPage clickQualificationDetailsLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString());
    }

    public AnnualAssessmentCertificatesIndexPage clickAnnualAssessmentCertificatesLink() {
        throw new UnsupportedOperationException("Operation not supported for: " + this.toString());
    }
}
