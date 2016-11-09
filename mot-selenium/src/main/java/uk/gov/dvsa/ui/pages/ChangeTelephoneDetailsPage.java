package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.UserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

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
        FormDataHelper.enterText(personTelephone, value);
        return this;
    }

    public ProfilePage submitAndReturnToProfilePage(ProfilePage page) {
        submitTelephone.click();
        return MotPageFactory.newPage(driver, page.getClass());
    }

    public ChangeTelephoneDetailsPage submit() {
        submitTelephone.click();
        return this;
    }

    public ProfilePage cancelEdit(ProfilePage page){
        cancelAndReturnToYourProfile.click();
        return MotPageFactory.newPage(driver, page.getClass());
    }

    public <T extends Page> T clickCancelButton(boolean isYourProfile){
        cancelAndReturnToYourProfile.click();
        if (isYourProfile){
            return (T)MotPageFactory.newPage(driver, PersonProfilePage.class);
        }
        return (T)MotPageFactory.newPage(driver, UserProfilePage.class);
    }

    public <T extends Page> T clickSubmitButton(Class<T> clazz) {
        submitTelephone.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }
}

