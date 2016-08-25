package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class LostForgottenCardQuestionOnePage extends AbstractLostForgottenPage {
    private static final String PAGE_TITLE = "First security question";
    public static final String PATH = "/lost-or-forgotten-card/question-one";

    @FindBy(id = "answer") private WebElement answerTextBox;
    @FindBy(id = "submit") private WebElement submitButton;

    public LostForgottenCardQuestionOnePage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public LostForgottenCardQuestionOnePage enterAnswer(String answer) {
        FormDataHelper.enterText(answerTextBox, answer);
        return this;
    }

    public LostForgottenCardQuestionTwoPage continueToQuestionTwoPage() {
        submitButton.click();
        return new LostForgottenCardQuestionTwoPage(driver);
    }
}
