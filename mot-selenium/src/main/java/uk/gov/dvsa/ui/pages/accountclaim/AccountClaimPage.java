package uk.gov.dvsa.ui.pages.accountclaim;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class AccountClaimPage extends Page {

    public static final String PATH = "/account/claim";
    private static final String PAGE_TITLE = "Claim your account";

    @FindBy(id = "email") private WebElement emailField;
    @FindBy(id = "confirm_email") private WebElement confirmEmailField;
    @FindBy(id = "emailOptOut") private WebElement emailOptOutCheckBox;
    @FindBy(id = "password") private WebElement passwordField;
    @FindBy(id = "confirm_password") private WebElement confirmPasswordField;
    @FindBy(id = "btSubmitForm") private WebElement submitFormButton;
    @FindBy(id = "question_a") private WebElement questionOneDropdown;
    @FindBy(id = "question_b") private WebElement questionTwoDropdown;
    @FindBy(id = "answer_a") private WebElement answerOneField;
    @FindBy(id = "answer_b") private WebElement answerTwoField;
    @FindBy(id = "claim-account-pin") private WebElement pinNumber;

    public AccountClaimPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AccountClaimPage enterEmailAndPassword(String email, String password) {
        FormDataHelper.enterText(emailField, email);
        FormDataHelper.enterText(confirmEmailField, email);

        FormDataHelper.enterText(passwordField, password);
        FormDataHelper.enterText(confirmPasswordField, password);

        return this;
    }

    public AccountClaimPage clickContinueButton() {
        submitFormButton.click();

        return this;
    }

    public AccountClaimPage setSecurityQuestionsAndAnswers(String answerOne, String answerTwo) {
        FormDataHelper.selectFromDropDownByValue(questionOneDropdown, "2");
        FormDataHelper.enterText(answerOneField, answerOne);

        FormDataHelper.selectFromDropDownByValue(questionTwoDropdown, "6");
        FormDataHelper.enterText(answerTwoField, answerTwo);

        return this;
    }

    public String getPinNumber() {
        return pinNumber.getText();
    }

    public boolean isPinNumberDisplayed() {
        return (pinNumber.isDisplayed());
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
