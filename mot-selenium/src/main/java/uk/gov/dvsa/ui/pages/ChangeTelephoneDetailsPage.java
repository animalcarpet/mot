package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormCompletionHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.profile.NewPersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;

public class ChangeTelephoneDetailsPage extends Page {

    private static final String PAGE_TITLE = "Change telephone number";

    @FindBy(id = "personTelephone") private WebElement personTelephone;
    @FindBy(id = "submit") private WebElement submitTelephone;
    @FindBy(id = "cancel-and-return") private WebElement cancelAndReturnToYourProfile;
    @FindBy(id = "validation-summary-id") private WebElement validationMessage;

    public ChangeTelephoneDetailsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeTelephoneDetailsPage fillTel(String value) {
        FormCompletionHelper.enterText(personTelephone, value);
        return this;
    }

    public <T extends Page> T clickSubmitButton(Class<T> clazz) {
        submitTelephone.click();
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

