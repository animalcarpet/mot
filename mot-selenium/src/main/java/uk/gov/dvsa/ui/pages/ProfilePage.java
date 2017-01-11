package uk.gov.dvsa.ui.pages;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.changedriverlicence.ChangeDrivingLicencePage;
import uk.gov.dvsa.ui.pages.dvsa.ManageRolesPage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;
import uk.gov.dvsa.ui.pages.profile.ChangeDateOfBirthPage;
import uk.gov.dvsa.ui.pages.profile.ChangeNamePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;
import uk.gov.dvsa.ui.pages.vts.SearchResultsPage;

public abstract class ProfilePage extends Page {
    private static String pageTitle;

    public ProfilePage(MotAppDriver driver, String pageTitle) {
        super(driver);
        this.pageTitle = pageTitle;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), pageTitle);
    }

    public boolean drivingLicenceIsDisplayed() {
        return false;
    }

    public boolean addEditDrivingLicenceLinkExists() {
        return false;
    }

    public ManageRolesPage clickManageRolesLink(){
        return new ManageRolesPage(driver);
    }

    public RolesAndAssociationsPage clickRolesAndAssociationsLink(){
        return null;
    }

    public String getDrivingLicenceForPerson() {
        return null;
    }

    public String getEmailAddressForPerson(){
        return null;
    }

    public ChangeDrivingLicencePage clickChangeDrivingLicenceLink() {
        return null;
    }

    public ChangeNamePage clickChangeNameLink() {
        return null;
    }

    public ChangeDateOfBirthPage clickChangeDOBLink() {
        return null;
    }

    public ChangeEmailDetailsPage clickChangeEmailLink(){
        return null;
    }

    public ChangeTelephoneDetailsPage clickChangeTelephoneLink(){
        return null;
    }

    public String getDrivingLicenceRegionForPerson() {
        return null;
    }

    public String getMessageSuccess(){
        return "";
    }

    public boolean isSuccessMessageDisplayed() {
        return false;
    }

    public boolean isTesterQualificationStatusDisplayed() {
        return false;
    }

    public boolean isDrivingLicenceInformationIsDisplayed() {
        return false;
    }

    public boolean isChangeDrivingLicenceLinkIsDisplayed() {
        return false;
    }

    public boolean isChangeDOBLinkIsDisplayed() {
        return false;
    }

    public boolean isRolesAndAssociationsLinkDisplayed() {
        return false;
    }

    public boolean isChangeEmailLinkIsDisplayed() {
        return false;
    }

    public boolean isDvsaRolesSectionIsDisplayed() {
        return false;
    }

    public boolean isQualificationStatusSectionIsDisplayed() {
        return false;
    }

    public boolean isAccountSecuritySectionDisplayed() {
        return false;
    }

    public boolean isAccountManagementSectionDisplayed() {
        return false;
    }

    public boolean isChangeQualificationLinksDisplayed() {
        return false;
    }

    public boolean isChangeNameLinkDisplayed() {
        return false;
    }

    public boolean isPageLoaded() {
        return false;
    }

    public UserSearchResultsPage clickCancelAndReturnToSearchResults() {
        return null;
    }
}
