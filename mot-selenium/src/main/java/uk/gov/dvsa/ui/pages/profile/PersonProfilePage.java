package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.Utilities;
import uk.gov.dvsa.ui.pages.ChangeEmailDetailsPage;
import uk.gov.dvsa.ui.pages.ChangePasswordFromProfilePage;
import uk.gov.dvsa.ui.pages.ChangeTelephoneDetailsPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardPage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.mot.TesterTestLogPage;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsPage;
import uk.gov.dvsa.ui.pages.profile.security.ChangeSecurityQuestionsPasswordPage;
import uk.gov.dvsa.ui.pages.profile.testqualityinformation.AggregatedTestQualityPage;

public class PersonProfilePage extends ProfilePage {

    public static final String PATH = "/your-profile";
    private static final String PAGE_TITLE = "Your profile";

    @FindBy(id = "personal_details") private WebElement personalDetails;
    @FindBy(css = "#date-of-birth a") protected WebElement changeDOBLink;
    @FindBy(css = "#display-name a") private WebElement changeNameLink;
    @FindBy(id = "contact_details") private WebElement contactDetails;
    @FindBy(id = "qualification_status") private WebElement qualificationStatus;
    @FindBy(id = "change-password") private WebElement changePasswordLink;
    @FindBy(id = "account_management") private WebElement accountManagement;
    @FindBy(id = "validation-message--success") private WebElement messageSuccess;
    @FindBy(id = "related") private WebElement related;
    @FindBy(id = "account_security") private WebElement accountSecurity;
    @FindBy(id = "roles-and-associations") private WebElement rolesAndAssociationsLink;
    @FindBy(id = "qualification-details") private WebElement qualificationDetails;
    @FindBy(css = "#email-address a") private WebElement changeEmailLink;
    @FindBy(css = "#full-address a") private WebElement changeAddressLink;
    @FindBy(css = "#email-address") private WebElement userEmail;
    @FindBy(css = "#telephone-number") private WebElement userTelephone;
    @FindBy(css = "#telephone-number a") private WebElement userTelephoneLink;
    @FindBy(css = "#validation-message--success") private WebElement successMessage;
    @FindBy(id = "reset-pin") private WebElement resetPinLink;
    @FindBy(id = "register-card") private WebElement activateCardLink;
    @FindBy(id = "security-card-order") private WebElement orderSecurityCardLink;
    private By securityCardPanel = By.id("security-card");
    @FindBy(id = "annual-assessment-certificates") WebElement annualAssessmentCertificates;
    @FindBy(id = "test-quality-information") private WebElement testQualityInformationLink;
    @FindBy(id = "change-security-questions") private WebElement changeSecurityQuestionsLink;
    @FindBy(id = "test-logs") private WebElement testLogsLink;

    private static String DVSA_ROLES = "dvsa_roles";
    private static String QUALIFICATION_STATUS = "qualification_status";
    private static String GROUP_A_QUALIFICATION = "change-group-a-qualification";
    private static String GROUP_B_QUALIFICATION = "change-group-b-qualification";

    public PersonProfilePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    @Override
    public RolesAndAssociationsPage clickRolesAndAssociationsLink() {
        rolesAndAssociationsLink.click();
        return new RolesAndAssociationsPage(driver);
    }

    @Override
    public ChangeEmailDetailsPage clickChangeEmailLink() {
        changeEmailLink.click();
        return new ChangeEmailDetailsPage(driver);
    }

    @Override
    public ChangePasswordFromProfilePage clickChangePasswordLink() {
        if(PageInteractionHelper.isElementDisplayed(changePasswordLink)) changePasswordLink.click();
        return new ChangePasswordFromProfilePage(driver);
    }

    public ChangeTelephoneDetailsPage clickChangeTelephoneLink() {
        userTelephoneLink.click();
        return new ChangeTelephoneDetailsPage(driver);
    }

    @Override
    public ChangeAddressPage clickChangeAddressLink() {
        changeAddressLink.click();
        return new ChangeAddressPage(driver);
    }

    public QualificationDetailsPage clickQualificationDetailsLink(){
        qualificationDetails.click();
        return new QualificationDetailsPage(driver);
    }

    @Override
    public RegisterCardPage activateCardLink() {
        activateCardLink.click();
        return new RegisterCardPage(driver);
    }

    public AnnualAssessmentCertificatesIndexPage clickAnnualAssessmentCertificatesLink() {
        annualAssessmentCertificates.click();
        return new AnnualAssessmentCertificatesIndexPage(driver);
    }

    public AggregatedTestQualityPage clickTestQualityInformationLink() {
        testQualityInformationLink.click();
        return new AggregatedTestQualityPage(driver);
    }

    public ChangeSecurityQuestionsPasswordPage clickChangeSecurityQuestionsLink() {
        if(!PageInteractionHelper.isElementDisplayed(changeSecurityQuestionsLink)) {
            Utilities.Logger.LogInfo("Security Questions link not found");
        } else {
            changeSecurityQuestionsLink.click();
        }
        return new ChangeSecurityQuestionsPasswordPage(driver);
    }

    public TesterTestLogPage clickTestLogsLink() {
        testLogsLink.click();
        return new TesterTestLogPage(driver);
    }

    public String getMessageSuccess(){
        return messageSuccess.getText();
    }

    @Override
    public boolean isChangeDrivingLicenceLinkIsDisplayed() {
        return !personalDetails.findElements(By.xpath(".//*[@id='drivingLicence']/a")).isEmpty();
    }

    @Override
    public boolean isChangeEmailLinkIsDisplayed() {
        return !contactDetails.findElements(By.xpath(".//*[@id='email-address']/a")).isEmpty();
    }

    @Override
    public boolean isSuccessMessageDisplayed() {
        return successMessage.isDisplayed();
    }

    @Override
    public boolean isChangeDOBLinkIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeDOBLink);
    }

    @Override
    public boolean isChangeNameLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeNameLink);
    }

    @Override
    public boolean isDvsaRolesSectionIsDisplayed() {
        return !driver.findElements(By.id(DVSA_ROLES)).isEmpty();
    }

    public boolean isQualificationStatusSectionIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(qualificationStatus);
    }

    @Override
    public boolean isDrivingLicenceInformationIsDisplayed() {
        return !personalDetails.findElements(By.id("drivingLicence")).isEmpty();
    }

    @Override
    public boolean isAccountSecuritySectionDisplayed() {
        return accountSecurity.isDisplayed();
    }

    @Override
    public boolean isAccountManagementSectionDisplayed() {
        return accountManagement.isDisplayed();
    }

    @Override
    public boolean isChangeQualificationLinksDisplayed() {
        return !related.findElements(By.id(GROUP_A_QUALIFICATION)).isEmpty() &&
                !related.findElements(By.id(GROUP_B_QUALIFICATION)).isEmpty();
    }

    @Override
    public boolean isTesterQualificationStatusDisplayed() {
        return qualificationStatus.isDisplayed();
    }

    @Override
    public boolean isOrderSecurityCardDisplayed(){
        return orderSecurityCardLink.isDisplayed();
    }

    @Override
    public boolean isRolesAndAssociationsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(rolesAndAssociationsLink);
    }

    public boolean isPageLoaded() {
        return selfVerify();
    }

    @Override
    public boolean isResetPinLinkDisplayed() { return PageInteractionHelper.isElementDisplayed(resetPinLink); }

    @Override
    public boolean isActivateSecurityCardLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(activateCardLink);
    }

    @Override
    public boolean isSecurityCardPanelDisplayed() {
        return PageInteractionHelper.isElementDisplayed(securityCardPanel);
    }

    public Boolean isChangeSecurityQuestionsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeSecurityQuestionsLink);
    }
}
