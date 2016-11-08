package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.*;
import uk.gov.dvsa.ui.pages.mot.TestShortSummaryPage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.vts.ChangeDetails.*;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfileOfUserPage;

public class VehicleTestingStationPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s";
    private static final String PAGE_TITLE = "Vehicle Testing Station";
    
    private static final String SITE_ASSESSMENT_LINK_ID = "site-assessment-action-link";

    @FindBy(className = "content-header__title") private WebElement pageHeader;
    @FindBy(className = "group") private WebElement breadCrumbList;
    @FindBy(id = "assign-a-role") private WebElement assignARoleLink;
    @FindBy(css = "div.text") private WebElement getRole;
    @FindBy(id = "validation-message--success") private WebElement validationMessageSuccess;
    @FindBy(id = "email") private WebElement emailValue;
    @FindBy(id = "phone-number") private WebElement phoneNumberValue;
    @FindBy(id = "remove-role") private WebElement removeTesterRole;
    @FindBy(id = "event-history") private WebElement viewEventHistoryLink;
    @FindBy(id = "site-address") private WebElement vtsAddress;
    @FindBy(id = "email") private WebElement vtsEmail;
    @FindBy(id = "phone-number") private WebElement vtsPhoneNumber;
    @FindBy(id = "edit-site-details") private WebElement editSiteDetails;
    @FindBy(id = "change-testing-facilities") private WebElement changeTestingFacilitiesLink;
    @FindBy(id = "testing-facility-optl") private WebElement onePersonTestLaneValue;
    @FindBy(id = "testing-facility-tptl") private WebElement twoPersonTestLaneValue;
    @FindBy(css = "#risk-assessment-score span.text-secondary") private WebElement riskAssesmentscore;
    @FindBy(css = "#risk-assessment-score span.badge") private WebElement riskAssesmentColor;
    @FindBy(id = SITE_ASSESSMENT_LINK_ID) private WebElement addSiteAssessmentLink;
    @FindBy(id = "risk-assessment-score") private WebElement riskAssessmentScore;
    @FindBy(id = "validation-message--success") private WebElement validationMessage;
    @FindBy(linkText = "Site assessment") private WebElement updateRiskAssessment;
    @FindBy(id = "authorisedExaminer") private WebElement authorisedExaminer;
    @FindBy(id = "site-number") private WebElement vtsNumber;
    @FindBy(id = "site-name") private WebElement vtsName;
    @FindBy(id = "change-site-details") private WebElement changeSiteDetailsLink;
    @FindBy(id = "test-classes") private WebElement vtsClasseValue;
    @FindBy(id = "site-type") private WebElement vtsTypeValue;
    @FindBy(id = "site-status") private WebElement vtsStatusValue;
    @FindBy(id = "site-country") private WebElement vtsCountryValue;
    @FindBy(id = "site-name-change") private WebElement changeVtsNameLink;
    @FindBy(id = "site-type-change") private WebElement changeVtsTypeLink;
    @FindBy(id = "site-classes-change") private WebElement changeVtsClassesLink;
    @FindBy(id = "site-status-change") private WebElement changeVtsStatusLink;
    @FindBy(id = "site-address-change") private WebElement changeVtsAddressLink;
    @FindBy(id = "site-country-change") private WebElement changeVtsCountryLink;
    @FindBy(id = "email-change") private WebElement changeVtsEmailLink;
    @FindBy(id = "phone-number-change") private WebElement changeVtsTelephoneLink;

    public VehicleTestingStationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleTestingStationPage assignARole() {
        assignARoleLink.click();

        return this;
    }

    public boolean isValidationMessageSuccessDisplayed() {
        return (validationMessageSuccess.isDisplayed());
    }

    public String getTesterName(String id) {
        By nameLocator = By.cssSelector(String.format(".table-summary a[href*='%s']", id));

        if (driver.findElements(nameLocator).isEmpty()) {
            return "Tester not found on this page";
        }

        return driver.findElement(nameLocator).getText();
    }

    public String getRole() {
        return getRole.getText();
    }

    public String getEmailValue() {
        return emailValue.getText();
    }

    public String getPhoneNumberValue() {
        return phoneNumberValue.getText();
    }

    public RemoveARolePage removeTesterRole(String id) {
        driver.findElement(By.cssSelector(String.format("#role-assignment-%s-TESTER td a", id))).click();
        return new RemoveARolePage(driver);
    }

    public void clickOnViewHistoryLink() {
        viewEventHistoryLink.click();
    }

    public boolean isTesterDisplayed(String id) {
        return !driver.findElements(By.cssSelector(String.format("a[href*='%s']", id))).isEmpty();
    }

    public boolean isAddressDisplayed() {
        return vtsAddress.isDisplayed();
    }

    public boolean isEmailDisplayed() {
        return vtsEmail.isDisplayed();
    }

    public boolean isPhoneNumberDisplayed() {
        return vtsPhoneNumber.isDisplayed();
    }

    public boolean isRiskAssesmentDisplayed() {
        return riskAssesmentscore.isDisplayed();
    }

    public boolean isAddSiteAssessmentLinkDisplayed() {
        if (driver.findElements(By.id(SITE_ASSESSMENT_LINK_ID)).isEmpty()) {
            return false;
        }

        return addSiteAssessmentLink.isDisplayed();
    }

    public boolean isActiveMotTestDisplayed(String vehicleRegistrationNumber) {
        return driver.findElement(By.linkText(vehicleRegistrationNumber)).getText()
                .contains(vehicleRegistrationNumber);
    }

    public TestShortSummaryPage clickOnActiveTest(String regNum) {
        driver.findElement(By.linkText(regNum)).click();
        return new TestShortSummaryPage(driver);
    }

    public ChangeTestingFacilitiesPage clickOnChangeTestingFacilitiesLink() {
        changeTestingFacilitiesLink.click();
        return new ChangeTestingFacilitiesPage(driver);
    }

    public String verifyOnePersonTestLaneValueDisplayed() {
        return onePersonTestLaneValue.getText();
    }

    public String verifyTwoPersonTestLaneValueDisplayed() {
        return twoPersonTestLaneValue.getText();
    }

    public String getTypeValue() {
        return vtsTypeValue.getText();
    }

    public String getNameValue() {
        return vtsName.getText();
    }

    public String getStatusValue() {
        return vtsStatusValue.getText();
    }

    public String getCountryValue() {
        return vtsCountryValue.getText();
    }

    public String getPageHeader() {
        return breadCrumbList.getText();
    }

    public String verifyRiskAssesmentScore() {
        return riskAssesmentscore.getText();
    }

    public AddSiteAssessmentPage clickOnAddSiteAssessmentLink() {
        addSiteAssessmentLink.click();
        return new AddSiteAssessmentPage(driver);
    }

    public String getRiskAssessmentScore() {
        return riskAssesmentscore.getText();
    }

    public String getValidationMessage() {
        System.out.println("validationMessage.getText() " + validationMessage.getText());
        return validationMessage.getText();
    }

    public boolean isUpdateSiteAssessmentLinkDisplayed() {
        return updateRiskAssessment.isDisplayed();
    }

    public SiteAssessmentPage clickOnUpdateSiteAssessmentLink() {
        updateRiskAssessment.click();
        return new SiteAssessmentPage(driver);
    }

    public String getAuthorisedExaminer() {
        return authorisedExaminer.getText();
    }

    public String getVtsNumber() {
        return vtsNumber.getText();
    }

    public String getVtsName() {
        return vtsName.getText();
    }

    public ProfilePage chooseAssignedToVtsUser(String userId) {
        driver.findElement(By.cssSelector(String.format("a[href*='%s']", userId))).click();
        if(ConfigHelper.isNewPersonProfileEnabled()) {
            return new NewUserProfilePage(driver);
        } else {
            return new ProfileOfUserPage(driver);
        }
    }

    public String getSiteAssessmentColour(String colourBadgeType) {
        String selector = String.format("#risk-assessment-score span.badge--%s", colourBadgeType);
        return driver.findElementByCssSelector(selector).getText();
    }

    public ChangeDetailsClassesPage clickOnChangeClassesLink() {
        changeVtsClassesLink.click();
        return new ChangeDetailsClassesPage(driver);
    }

    public ChangeDetailsNamePage clickOnChangeNameLink() {
        changeVtsNameLink.click();
        return new ChangeDetailsNamePage(driver);
    }

    public ChangeDetailsStatusPage clickOnChangeStatusLink() {
        changeVtsStatusLink.click();
        return new ChangeDetailsStatusPage(driver);
    }

    public ChangeDetailsTypePage clickOnChangeTypeLink() {
        changeVtsTypeLink.click();
        return new ChangeDetailsTypePage(driver);
    }

    public ChangeDetailsAddressPage clickOnChangeAddressLink() {
        changeVtsAddressLink.click();
        return new ChangeDetailsAddressPage(driver);
    }

    public ChangeDetailsCountryPage clickOnChangeCountryLink() {
        changeVtsCountryLink.click();
        return new ChangeDetailsCountryPage(driver);
    }

    public ChangeDetailsEmailPage clickOnChangeEmailLink() {
        changeVtsEmailLink.click();
        return new ChangeDetailsEmailPage(driver);
    }

    public ChangeDetailsTelephonePage clickOnChangeTelephoneLink() {
        changeVtsTelephoneLink.click();
        return new ChangeDetailsTelephonePage(driver);
    }

    public boolean isChangeEmailLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeVtsEmailLink);
    }

    public boolean isChangeTelephoneLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeVtsTelephoneLink);
    }

    public boolean isChangeAddressLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeVtsAddressLink);
    }

    public boolean isChangeCountryLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeVtsCountryLink);
    }
}
