package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SecurityQuestionsPage extends Page {

    private static final String PAGE_TITLE = "Your security questions";

    @FindBy(id = "continue") private WebElement continueToNextPage;
    @FindBy(id = "question1") private WebElement securityQDropDown1;
    @FindBy(id = "answer1") private WebElement securityQAnswer1;
    @FindBy(id = "question2") private WebElement securityQDropDown2;
    @FindBy(id = "answer2") private WebElement securityQAnswer2;

    public SecurityQuestionsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public PasswordPage clickContinue() {
        continueToNextPage.click();
        return new PasswordPage(driver);
    }

    public SecurityQuestionsPage chooseQuestionsAndAnswers()
    {
        FormDataHelper.selectFromDropDownByValue(securityQDropDown1, "1");
        FormDataHelper.enterText(securityQAnswer1, "Answer");
        FormDataHelper.selectFromDropDownByValue(securityQDropDown2, "6");
        FormDataHelper.enterText(securityQAnswer2, "Answer");
        return this;
    }
}
