package uk.gov.dvsa.ui.pages.profile.security;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeSecurityQuestionOnePage extends Page {

    private static final String PAGE_TITLE = "First security question";

    @FindBy(id = "continue") private WebElement continueToNextPage;

    @FindBy(id = "questions") private WebElement questionDropDown;

    @FindBy(id = "question-answer") private WebElement answer;

    public ChangeSecurityQuestionOnePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ChangeSecurityQuestionTwoPage clickContinue() {
        continueToNextPage.click();
        return new ChangeSecurityQuestionTwoPage(driver);
    }

    public ChangeSecurityQuestionOnePage chooseQuestionAndAnswer()
    {
        FormDataHelper.selectFromDropDownByValue(questionDropDown, "1");
        FormDataHelper.enterText(answer, "Answer");
        return this;
    }
}
