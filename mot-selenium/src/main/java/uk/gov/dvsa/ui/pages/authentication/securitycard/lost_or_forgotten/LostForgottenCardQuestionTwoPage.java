package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class LostForgottenCardQuestionTwoPage extends AbstractLostForgottenPage {

    private static final String PAGE_TITLE = "Second security question";
    public static final String PATH = "/lost-or-forgotten-card/question-two";

    @FindBy(id = "answer")
    private WebElement answerTextBox;
    @FindBy(id = "submit")
    private WebElement submitButton;

    public LostForgottenCardQuestionTwoPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public LostForgottenCardQuestionTwoPage enterAnswer(String answer) {
        FormDataHelper.enterText(answerTextBox, answer);
        return this;
    }

    public LostForgottenCardConfirmationPage continueToConfirmationPage() {
        submitButton.click();
        return new LostForgottenCardConfirmationPage(driver);
    }
}
