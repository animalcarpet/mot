package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SummaryPage extends Page {

    private static final String PAGE_TITLE = "Review your account details";

    @FindBy(id = "continue") private WebElement createYourAccount;

    @FindBy (id = "back") private WebElement cancelAndReturn;

    @FindBy(id = "duplicatedEmailAddress") private WebElement duplicateEmailMessage;

    public SummaryPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public AccountCreatedPage clickCreateYourAccount() {
        createYourAccount.click();
        return new AccountCreatedPage(driver);
    }

    public boolean emailAlreadyUsedMessage() {
        return PageInteractionHelper.isElementDisplayed(duplicateEmailMessage);
    }
}
