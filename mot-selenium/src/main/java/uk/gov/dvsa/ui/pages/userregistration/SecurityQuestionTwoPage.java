package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SecurityQuestionTwoPage extends Page {

    private static final String PAGE_TITLE = "Second security question";

    @FindBy(id = "continue") private WebElement continueToNextPage;

    @FindBy(id = "question2") private WebElement securityQDropDown;

    @FindBy(id = "answer2") private WebElement securityQAnswer;

    public SecurityQuestionTwoPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public PasswordPage selectQuestionAndEnterAnswerExpectingPasswordPage(String securityQuestionTwoSelected, String answer){
        FormDataHelper.selectFromDropDownByValue(securityQDropDown, securityQuestionTwoSelected);
        FormDataHelper.enterText(securityQAnswer, answer);
        continueToNextPage.click();
        return new PasswordPage(driver);
    }

    public PasswordPage clickContinue() {
        continueToNextPage.click();
        return new PasswordPage(driver);
    }

    public SecurityQuestionTwoPage chooseQuestionAndAnswer()
    {
        FormDataHelper.selectFromDropDownByValue(securityQDropDown, "6");
        FormDataHelper.enterText(securityQAnswer, "Answer");
        return this;
    }
}
