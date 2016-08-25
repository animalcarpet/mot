package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.TwoFactorPromptPage;
import uk.gov.dvsa.ui.pages.Page;

public class ActivateYourCardPromptPage extends Page implements TwoFactorPromptPage {
    private static final String PAGE_TITLE = "Activate your security card";
    public static final String PATH= "/security-card-information-new-user/%s";

    @FindBy(id = "activate-message") private WebElement message;

    public ActivateYourCardPromptPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    @Override
    public String getText() {
        return message.getText();
    }
}
