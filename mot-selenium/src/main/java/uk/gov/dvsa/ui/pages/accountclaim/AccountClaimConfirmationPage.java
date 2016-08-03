package uk.gov.dvsa.ui.pages.accountclaim;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class AccountClaimConfirmationPage extends Page {

    private static final String PAGE_TITLE = "Claim account confirmation";

    @FindBy(id = "go-to-home") private WebElement continueToMotTestingServiceButton;

    @FindBy(id = "claim-account-pin") private WebElement claimAccountPin;

    @FindBy(className = "banner__heading") private WebElement pinHeading;

    @FindBy(className = "lead") private WebElement leadHeading;

    @FindBy(className = "text") private WebElement pageContentText;

    public AccountClaimConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public HomePage clickContinueToTheMotTestingService() {
        continueToMotTestingServiceButton.click();
        return new HomePage(driver);
    }

    public String getPinHeadingText() {
        return pinHeading.getText();
    }

    public String getLeadHeadingText() {
        return leadHeading.getText();
    }

    public String getPageContentText() {
        return pageContentText.getText();
    }

    public boolean isPinNumberDisplayed() {
        return claimAccountPin.isDisplayed();
    }
}
