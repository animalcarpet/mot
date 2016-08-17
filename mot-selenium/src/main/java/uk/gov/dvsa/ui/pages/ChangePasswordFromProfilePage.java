package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.profile.NewPersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class ChangePasswordFromProfilePage extends Page {

    public static final String PATH = "/your-profile/change-password";
    public static final String PAGE_TITLE = "Your profile\n" + "Change your password";

    @FindBy(id = "oldPassword") private WebElement oldPassword;
    @FindBy(id = "password") private WebElement newPassword;
    @FindBy(id = "passwordConfirm") private WebElement passwordConfirm;
    @FindBy(id = "submitPass") private WebElement submitButton;
    @FindBy(id = "cancelLink") private WebElement cancelLink;
    private By errorMassagesWindowSelector = By.id("validation-summary-id");

    public ChangePasswordFromProfilePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangePasswordFromProfilePage enterOldPassword(String password) {
        FormDataHelper.enterText(oldPassword, password);
        return this;
    }

    public ChangePasswordFromProfilePage enterNewPassword(String password) {
        FormDataHelper.enterText(newPassword, password);
        return this;
    }

    public ChangePasswordFromProfilePage confirmNewPassword(String password) {
        FormDataHelper.enterText(passwordConfirm, password);
        return this;
    }

    public ProfilePage clickCancelLink() {
        cancelLink.click();
        return MotPageFactory.getProfilePageInstance(new NewPersonProfilePage(driver), new PersonProfilePage(driver));
    }

    public <T extends Page> T clickSubmitButton(Class<T> clazz) {
        submitButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public boolean isErrorMessageWindowDisplayed() {
        return isElementVisible(errorMassagesWindowSelector);
    }

    public String getErrorMessage() {
        return getElementText(errorMassagesWindowSelector);
    }
}
