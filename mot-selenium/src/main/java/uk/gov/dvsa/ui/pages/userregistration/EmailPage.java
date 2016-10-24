package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EmailPage extends Page {

    private static final String PAGE_TITLE = "Your email address";

    @FindBy(id = "emailAddress")
    private WebElement emailAddress;

    @FindBy(id = "confirmEmailAddress")
    private WebElement verifyEmailAddress;

    @FindBy(id = "continue")
    private WebElement continueToNextPage;


    public EmailPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public AddressPage enterDetailsAndSubmitExpectingDetailsPage(String email) {
        FormDataHelper.enterText(emailAddress, email);
        FormDataHelper.enterText(verifyEmailAddress, email);
        continueToNextPage.click();
        return new AddressPage(driver);
    }

    public DetailsPage clickContinue() {
        continueToNextPage.click();
        return new DetailsPage(driver);
    }

    public EmailPage enterYourDetails(String email, String confirmEmail) {
        FormDataHelper.enterText(emailAddress, email);
        FormDataHelper.enterText(verifyEmailAddress, email);

        return this;
    }

    public DuplicateEmailPage clickContinueWithEmailAlreadyInUse() {
        continueToNextPage.click();
        return new DuplicateEmailPage(driver);
    }
}
