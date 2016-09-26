package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.ChangeEmailDetailsPage;
import uk.gov.dvsa.ui.pages.ChangeTelephoneDetailsPage;
import uk.gov.dvsa.ui.pages.changedriverlicence.ChangeDrivingLicencePage;
import uk.gov.dvsa.ui.pages.dvsa.ManageRolesPage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsPage;
import uk.gov.dvsa.ui.pages.profile.testqualityinformation.AggregatedTestQualityPage;

public class NewUserProfilePage extends ProfilePage {
    private static final String PAGE_TITLE = "User profile";
    public static final String PATH = "/user-admin/user/%s";

    @FindBy(id = "manage-roles") private WebElement selectManageRolesLink;
    @FindBy(css = "#display-name a") private WebElement changeNameLink;
    @FindBy(id = "roles-and-associations") private WebElement roleAndAssociationLink;
    @FindBy(id = "drivingLicence") private WebElement drivingLicence;
    @FindBy(id = "account_management") private WebElement accountManagement;
    @FindBy(id = "qualification_status") private WebElement qualificationStatus;
    @FindBy(id = "dvsa_roles") private WebElement dvsaRoles;
    @FindBy(id = "change-group-a-qualification") private WebElement groupA_qualification;
    @FindBy(id = "change-group-b-qualification") private WebElement groupB_qualification;
    @FindBy(id = "personal_details") private WebElement personalDetailsTable;
    @FindBy(id = "dateOfBirth") private WebElement dateOfBirth;
    @FindBy(id = "qualification-details") private WebElement qualificationDetails;
    @FindBy(css = "#drivingLicence a") protected WebElement changeDrivingLicenceLink;
    @FindBy(css = "#email-address a") protected WebElement changeEmailLink;
    @FindBy(css = "#telephone-number a") protected WebElement changeTelephoneLink;
    @FindBy(css = "#telephone-number") protected WebElement userTelephone;
    @FindBy(css = "#date-of-birth a") protected WebElement changeDOBLink;
    @FindBy(css = "#email-address") private WebElement userEmail;
    @FindBy(css = "#drivingLicence span") protected WebElement personDrivingLicenceRegion;
    @FindBy(xpath = "(//*[@class='content-navigation__secondary']//a)[1]") protected WebElement cancelAndReturnToSearchResults;
    @FindBy(css = "#full-address a") private WebElement changeAddressLink;
    private By messageSuccessSelector = By.id("validation-message--success");
    @FindBy(id = "security-card-order") private WebElement orderSecurityCardLink;
    @FindBy(id = "management-order-card") private WebElement cscoOrderSecurityCardLink;
    @FindBy(id = "event-history") private WebElement eventHistoryLink;
    @FindBy(id = "test-quality-information") private WebElement testQualityInformationLink;
    @FindBy(id = "change-security-questions") private WebElement changeSecurityQuestionsLink;

    private By securityCardPanel = By.id("security-card");

    public NewUserProfilePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    @Override
    public ManageRolesPage clickManageRolesLink() {
        selectManageRolesLink.click();
        return new ManageRolesPage(driver);
    }

    @Override
    public boolean isRolesAndAssociationsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(roleAndAssociationLink);
    }

    @Override
    public boolean drivingLicenceIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(drivingLicence);
    }

    @Override
    public String getDrivingLicenceForPerson() {
        return drivingLicence.getText();
    }

    @Override
    public boolean addEditDrivingLicenceLinkExists() {
        return PageInteractionHelper.isElementDisplayed(changeDrivingLicenceLink);
    }

    @Override
    public String getMessageSuccess() {
        return getElementText(messageSuccessSelector);
    }

    @Override
    public boolean isSuccessMessageDisplayed() {
        return isElementVisible(messageSuccessSelector);
    }

    public boolean isQualificationStatusSectionIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(qualificationStatus);
    }

    @Override
    public boolean isOrderSecurityCardDisplayed() {
        return PageInteractionHelper.isElementDisplayed(orderSecurityCardLink);
    }

    public ChangeDrivingLicencePage clickChangeDrivingLicenceLink() {
        changeDrivingLicenceLink.click();
        return new ChangeDrivingLicencePage(driver);
    }

    @Override
    public boolean isChangeDrivingLicenceLinkIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeDrivingLicenceLink);
    }

    @Override
    public boolean isChangeDOBLinkIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeDOBLink);
    }

    @Override
    public boolean isDrivingLicenceInformationIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(drivingLicence);
    }

    @Override
    public boolean isAccountManagementSectionDisplayed() {
        return accountManagement.isDisplayed();
    }

    @Override
    public boolean isChangeQualificationLinksDisplayed() {
        return PageInteractionHelper.isElementDisplayed(groupA_qualification) &&
                PageInteractionHelper.isElementDisplayed(groupB_qualification);
    }

    @Override
    public boolean isChangeEmailLinkIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeEmailLink);
    }

    @Override
    public boolean isDvsaRolesSectionIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(dvsaRoles);
    }

    @Override
    public RolesAndAssociationsPage clickRolesAndAssociationsLink() {
        roleAndAssociationLink.click();
        return new RolesAndAssociationsPage(driver);
    }

    @Override
    public boolean isChangeNameLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeNameLink);
    }

    @Override
    public boolean isSecurityCardPanelDisplayed() {
        return PageInteractionHelper.isElementDisplayed(securityCardPanel);
    }

    public ChangeNamePage clickChangeNameLink() {
        changeNameLink.click();
        return new ChangeNamePage(driver);
    }

    public ChangeDateOfBirthPage clickChangeDOBLink() {
        changeDOBLink.click();
        return new ChangeDateOfBirthPage(driver);
    }

    @Override
    public ChangeEmailDetailsPage clickChangeEmailLink() {
        changeEmailLink.click();
        return new ChangeEmailDetailsPage(driver);
    }

    @Override
    public ChangeTelephoneDetailsPage clickChangeTelephoneLink() {
        changeTelephoneLink.click();
        return new ChangeTelephoneDetailsPage(driver);
    }

    @Override
    public ChangeAddressPage clickChangeAddressLink() {
        changeAddressLink.click();
        return new ChangeAddressPage(driver);
    }

    public boolean isPageLoaded() {
        return selfVerify();
    }

    @Override
    public UserSearchResultsPage clickCancelAndReturnToSearchResults() {
        cancelAndReturnToSearchResults.click();
        return MotPageFactory.newPage(driver, UserSearchResultsPage.class);
    }

    public QualificationDetailsPage clickQualificationDetailsLink() {
        qualificationDetails.click();
        return MotPageFactory.newPage(driver, QualificationDetailsPage.class);
    }

     public AggregatedTestQualityPage clickTestQualityInformationLink() {
         testQualityInformationLink.click();
         return new AggregatedTestQualityPage(driver);
     }

    public void clickOrderCardLink() {
        if (PageInteractionHelper.isElementDisplayed(cscoOrderSecurityCardLink)) {
            cscoOrderSecurityCardLink.click();
        }
    }

    public EventsHistoryPage clickEventHistoryLink() {
        if (PageInteractionHelper.isElementDisplayed(eventHistoryLink)) {
            eventHistoryLink.click();
        }
        return new EventsHistoryPage(driver);
    }

    public Boolean isChangeSecurityQuestionsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeSecurityQuestionsLink);
    }
}