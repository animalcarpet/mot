package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class DuplicateEmailPage extends Page {

    private static final String PAGE_TITLE = "This email is already in use";

    @FindBy(className = "content-header__title") private WebElement emailAddressAlreadyInUse;

    public DuplicateEmailPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

public String getMessageText() {
    return emailAddressAlreadyInUse.getText();
}

}
