package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class ForgottenPasswordQuestionOnePage extends AbstractForgottenPasswordPage {
    private static final String PAGE_TITLE = "First security question";

    @FindBy(id = "question1") private WebElement answerTextBox;
    @FindBy(id = "submitSecurityQuestion") private WebElement submitButton;

    public ForgottenPasswordQuestionOnePage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ForgottenPasswordQuestionOnePage enterAnswer(String answer) {
        FormDataHelper.enterText(answerTextBox, answer);
        return this;
    }

    public ForgottenPasswordQuestionTwoPage continueToQuestionTwoPage() {
        submitButton.click();
        return new ForgottenPasswordQuestionTwoPage(driver);
    }
}
