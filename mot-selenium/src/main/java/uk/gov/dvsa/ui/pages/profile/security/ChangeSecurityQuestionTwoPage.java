package uk.gov.dvsa.ui.pages.profile.security;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeSecurityQuestionTwoPage extends Page {

    private static final String PAGE_TITLE = "Second security question";

    @FindBy(id = "continue") private WebElement continueToNextPage;

    @FindBy(id = "questions") private WebElement questionDropDown;

    @FindBy(id = "question-answer") private WebElement answer;

    public ChangeSecurityQuestionTwoPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ChangeSecurityQuestionsReviewPage clickContinue() {
        continueToNextPage.click();
        return new ChangeSecurityQuestionsReviewPage(driver);
    }

    public ChangeSecurityQuestionTwoPage chooseQuestionAndAnswer()
    {
        FormDataHelper.selectFromDropDownByValue(questionDropDown, "6");
        FormDataHelper.enterText(answer, "Answer");
        return this;
    }
}
