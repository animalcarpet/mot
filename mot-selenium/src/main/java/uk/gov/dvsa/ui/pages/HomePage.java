package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.util.List;

public class HomePage extends Page {

    public static final String PATH = "/";
    private static final String PAGE_TITLE = "Your home";

    @FindBy(id = "header-user-name") private WebElement userNameHeader;
    @FindBy(id = "hero-actions") private WebElement heroSideBar;
    @FindBy(css = ".ae-title a[href*=authorised]") private WebElement aeTitle;
    @FindBy(css = ".ae-header p") private WebElement aeNumber;
    @FindBy(css = ".vts-list .vts-roles") private WebElement roleType;
    @FindBy(css = ".site-link") private WebElement siteName;
    @FindBy(id = "action-resume-mot-test") private WebElement resumeMotTestButton;
    @FindBy(id = "header_title") private WebElement vtsActivityLabel;
    @FindBy(id = "user-profile") private WebElement profileLink;
    @FindBy(id = "mot-test-certificates-list") private WebElement motCertificateList;
    @FindBy(id = "action-start-certificate-reissue") private WebElement StartCertificateReissue;
    @FindBy(css = "#notifications a") private WebElement notificationMessage;
    @FindBy(id = "action-start-user-search") private WebElement userSearchLink;
    @FindBy(id = "action-start-survey-reports") private WebElement generateSurveyReportsLink;
    @FindBy(id = "action-security-card-order-report-list") private WebElement securityCardOrderListLink;
    @FindBy(css = "a[title*='order a security card']") private WebElement orderCardNotificationLink;
    @FindBy(css = "a[title*='You have ordered a security card']") private WebElement orderSecurityCardSuccessNotificationLink;
    @FindBy(css = "a[title*='activate your security card']") private WebElement activateCardNotificationLink;
    @FindBy(xpath = "//script[contains(text(),'userId') and contains(text(), 'dataLayer')]") private WebElement googleTagManagerDataLayer;
    @FindBy(id = "action-start-non-mot") private WebElement startNonMotTestButton;
    @FindBy(linkText = "Vehicle information") private WebElement vehicleInformationLink;

    private static final By ROLE_NOMINATION_LIST = By.cssSelector("#notifications a");

    private List<WebElement> getTesterNominationElements() {
        return driver.findElements(ROLE_NOMINATION_LIST);
    }

    public HomePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE)
                && userNameHeader.getText().equals(driver.getCurrentUser().getNamesAndSurname());
    }

    public HomePage refresh() {
        driver.navigate().refresh();
        return new HomePage(driver);
    }

    public SiteNotificationPage clickOnNomination(){
        if (PageInteractionHelper.isElementDisplayed(ROLE_NOMINATION_LIST)) {
            driver.findElement(ROLE_NOMINATION_LIST).click();
        }
        return new SiteNotificationPage(driver);
    }

    public VehicleTestingStationPage selectRandomVts() {
        siteName.click();

        return new VehicleTestingStationPage(driver);
    }

    public VehicleSearchPage clickStartNonMotButton() {
        startNonMotTestButton.click();
        return new VehicleSearchPage(driver);
    }

    public VehicleInformationSearchPage clickVehicleInformationLink() {
        vehicleInformationLink.click();
        return new VehicleInformationSearchPage(driver);
    }

    public void clickActivateCardNotificationLink() {
        if(PageInteractionHelper.isElementDisplayed(activateCardNotificationLink)) {
            activateCardNotificationLink.click();
        }
    }

    public void clickOrderCardNotificationLink() {
        if(PageInteractionHelper.isElementDisplayed(orderCardNotificationLink)) {
            orderCardNotificationLink.click();
        }
    }

    public String getAeName() {
        return aeTitle.getText();
    }

    public String getSiteName() {
        return siteName.getText();
    }

    public String getRole() {
        return roleType.getText();
    }

    public boolean isOrderSecurityCardSuccessNotificationLinkPresent() {
        return PageInteractionHelper.isElementDisplayed(orderSecurityCardSuccessNotificationLink);
    }

    public boolean isHeroSideBarDisplayed() {
        return PageInteractionHelper.isElementDisplayed(heroSideBar);
    }

    public boolean isResumeMotTestDisplayed() {
        return PageInteractionHelper.isElementDisplayed(resumeMotTestButton);
    }

    public boolean isGenerateSurveyReportsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(generateSurveyReportsLink);
    }

    public boolean isSecurityCardOrderListLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(securityCardOrderListLink);
    }

    public boolean isGoogleTagManagerDataLayerRendered() {
        return googleTagManagerDataLayer.isEnabled();
    }

    public PersonProfilePage clickYourProfile() {
        profileLink.click();
        return new PersonProfilePage(driver);
    }
}
