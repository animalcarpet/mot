package uk.gov.dvsa.ui.pages.accountclaim;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.SecurityQuestion;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class AccountClaimSecurityQuestionsPage extends Page {

    public static final String PATH = "/account/claim";
    private static final String PAGE_TITLE = "Choose new security questions";

    @FindBy(id = "btSubmitForm") private WebElement submitFormButton;
    @FindBy(id = "question_a") private WebElement questionOneDropdown;
    @FindBy(id = "question_b") private WebElement questionTwoDropdown;
    @FindBy(id = "answer_a") private WebElement answerOneField;
    @FindBy(id = "answer_b") private WebElement answerTwoField;

    public AccountClaimSecurityQuestionsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AccountClaimSecurityQuestionsPage clickContinueButton() {
        submitFormButton.click();

        return this;
    }

    public AccountClaimSecurityQuestionsPage setSecurityQuestionsAndAnswers(String answerOne, String answerTwo) {
        FormDataHelper.selectFromDropDownByValue(questionOneDropdown, SecurityQuestion.FIRST_KISS.optionValue);
        FormDataHelper.enterText(answerOneField, answerOne);

        FormDataHelper.selectFromDropDownByValue(questionTwoDropdown, SecurityQuestion.FIRST_SCHOOL_TRIP.optionValue);
        FormDataHelper.enterText(answerTwoField, answerTwo);

        return this;
    }

    public HomePage clickSaveAndContinue() {
        submitFormButton.click();

        return new HomePage(driver);
    }

    public AccountClaimReviewPage clickContinueToAccountReview() {
        submitFormButton.click();

        return new AccountClaimReviewPage(driver);
    }
}
