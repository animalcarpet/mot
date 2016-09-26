package uk.gov.dvsa.ui.pages.profile.security;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeSecurityQuestionsPasswordPage extends Page {
    private static final String PAGE_TITLE = "Change security questions";
    public static final String PATH = "/your-profile/change-security-questions";

    @FindBy(id = "Password") private WebElement passwordField;
    @FindBy(id = "submitPasswordConfirmation") private WebElement continueButton;

    public ChangeSecurityQuestionsPasswordPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ChangeSecurityQuestionOnePage submitPassword(String value) {
        FormDataHelper.enterText(passwordField, value);
        continueButton.click();

        return new ChangeSecurityQuestionOnePage(driver);
    }
}
