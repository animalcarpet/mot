package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AccountNotCreatedPage extends Page {

    private static final String PAGE_TITLE = "Your account has not been created";

    @FindBy(id = "failBanner") private WebElement accountNotCreatedText;

    @FindBy(partialLinkText = "create your account") private WebElement createYourAccountLink;

    public AccountNotCreatedPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public SummaryPage clickBackToSummaryPage() {
        createYourAccountLink.click();
        return new SummaryPage(driver);
    }

    public boolean isAccountCreatedTextDisplayed(){
        return accountNotCreatedText.isDisplayed();
    }

}
