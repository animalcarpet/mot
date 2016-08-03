package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.profile.NewPersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;

public class ChangeEmailDetailsPage extends Page {

    private static final String PAGE_TITLE = "Change email address";

    @FindBy(id = "email") private WebElement email;
    @FindBy(id = "emailConfirm") private WebElement emailConfirm;
    @FindBy(id = "submitEmailAddress") private WebElement submitEmailAddress;
    @FindBy(id = "navigation-link-") private WebElement cancelAndReturnToYourProfile;
    @FindBy (id = "validation-summary-id") private WebElement validationMessage;

    public ChangeEmailDetailsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeEmailDetailsPage fillEmail(String value) {
        FormDataHelper.enterText(email, value);
        return this;
    }

    public ChangeEmailDetailsPage fillEmailConfirmation(String value) {
        FormDataHelper.enterText(emailConfirm, value);
        return this;
    }

    public <T extends Page> T clickSubmitButton(Class<T> clazz) {
        submitEmailAddress.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T clickCancelButton(boolean isYourProfile){
        cancelAndReturnToYourProfile.click();
        if (isYourProfile){
            return (T)MotPageFactory.newPage(driver, NewPersonProfilePage.class);
        }
        return (T)MotPageFactory.newPage(driver, NewUserProfilePage.class);
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }

}

