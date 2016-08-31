package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.util.List;

public class HomePage extends Page {

    public static final String PATH = "/";
    private static final String PAGE_TITLE = "Your home";

    @FindBy(className = "global-header_name") private WebElement userNameHeader;
    @FindBy(className = "hero-actions") private WebElement heroSideBar;
    @FindBy(css = ".pivot-panel_title a[href*=authorised]") private WebElement aeTitle;
    @FindBy(css = ".pivot-panel_header p") private WebElement aeNumber;
    @FindBy(css = ".pivot-panel_meta-list span") private WebElement roleType;
    @FindBy(css = ".site-link") private WebElement siteName;
    @FindBy(id = "action-resume-mot-test") private WebElement resumeMotTestButton;
    @FindBy(id = "header_title") private WebElement vtsActivityLabel;
    @FindBy(id = "mot-test-certificates-list") private WebElement motCertificateList;
    @FindBy(id = "action-start-certificate-reissue") private WebElement StartCertificateReissue;
    @FindBy(className = "notification_link") private WebElement notificationMessage;
    @FindBy(id = "action-start-user-search") private WebElement userSearchLink;
    @FindBy(id = "action-start-survey-reports") private WebElement generateSurveyReportsLink;
    @FindBy(id = "action-security-card-order-report-list") private WebElement securityCardOrderListLink;
    @FindBy(css = "a[title*='order a security card']") private WebElement orderCardNotificationLink;
    @FindBy(css = "a[title*='You have ordered a security card']") private WebElement orderSecurityCardSuccessNotificationLink;
    @FindBy(css = "a[title*='activate your security card']") private WebElement activateCardNotificationLink;
    @FindBy(xpath = "//script[contains(text(),'userId') and contains(text(), 'dataLayer')]") private WebElement googleTagManagerDataLayer;

    private static final By ROLE_NOMINATION_LIST = By.cssSelector(".notification_subject > a");

    private List<WebElement> getTesterNominationElements() {
        return driver.findElements(ROLE_NOMINATION_LIST);
    }

    public HomePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public HomePage refresh() {
        driver.navigate().refresh();
        return new HomePage(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE)
                && userNameHeader.getText().equals(driver.getCurrentUser().getNamesAndSurname());
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

    public boolean isOrderSecurityCardSuccessNotificationLinkPresent()
    {
        return PageInteractionHelper.isElementDisplayed(orderSecurityCardSuccessNotificationLink);
    }

    public HomePage clickOnLastNomination() {
        getTesterNominationElements().get(0).click();
        return this;
    }

    public SiteNotificationPage clickOnNomination(){
        if (PageInteractionHelper.isElementDisplayed(ROLE_NOMINATION_LIST)) {
            driver.findElement(ROLE_NOMINATION_LIST).click();
        }
        return new SiteNotificationPage(driver);
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

    public VehicleTestingStationPage selectRandomVts() {
        siteName.click();

        return new VehicleTestingStationPage(driver);
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
}
