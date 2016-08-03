package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SecurityQuestionOnePage extends Page {

    private static final String PAGE_TITLE = "First security question";

    @FindBy(id = "continue") private WebElement continueToNextPage;

    @FindBy(id = "question1") private WebElement securityQDropDown;

    @FindBy(id = "answer1") private WebElement securityQAnswer;

    public SecurityQuestionOnePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public SecurityQuestionTwoPage selectQuestionAndEnterAnswerExpectingSecurityQuestionTwoPage(String securityQ1selected, String answer){
        FormDataHelper.selectFromDropDownByValue(securityQDropDown, securityQ1selected);
        FormDataHelper.enterText(securityQAnswer, answer);
        continueToNextPage.click();
        return new SecurityQuestionTwoPage(driver);
    }

    public SecurityQuestionTwoPage clickContinue() {
        continueToNextPage.click();
        return new SecurityQuestionTwoPage(driver);
    }

    public SecurityQuestionOnePage chooseQuestionAndAnswer()
    {
        FormDataHelper.selectFromDropDownByValue(securityQDropDown, "1");
        FormDataHelper.enterText(securityQAnswer, "Answer");
        return this;
    }
}
