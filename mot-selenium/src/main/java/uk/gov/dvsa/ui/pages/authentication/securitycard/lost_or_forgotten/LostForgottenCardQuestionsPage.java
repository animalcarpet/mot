package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class LostForgottenCardQuestionsPage extends AbstractLostForgottenPage {
    private static final String PAGE_TITLE = "First security question";
    public static final String PATH = "/lost-or-forgotten-card/question-one";

    @FindBy(id = "answer1") private WebElement answerOneTextBox;
    @FindBy(id = "answer2") private WebElement answerTwoTextBox;
    @FindBy(id = "submit") private WebElement submitButton;

    public LostForgottenCardQuestionsPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public LostForgottenCardQuestionsPage enterAnswers(String answer1, String answer2) {
        FormDataHelper.enterText(answerOneTextBox, answer1);
        FormDataHelper.enterText(answerTwoTextBox, answer2);
        return this;
    }

    public LostForgottenCardConfirmationPage continueToConfirmationPage() {
        submitButton.click();
        return new LostForgottenCardConfirmationPage(driver);
    }
}
