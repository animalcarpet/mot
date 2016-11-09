package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeNamePage extends Page {

    private static final String PAGE_TITLE = "Change name";

    @FindBy (id = "firstName") private WebElement firstNameInput;
    @FindBy (id = "middleName") private WebElement middleNameInput;
    @FindBy (id = "lastName") private WebElement lastNameInput;
    @FindBy (id = "submitNameChange") private WebElement submitNameChangeButton;
    @FindBy (id = "cancel-and-return") private WebElement cancelAndReturnLink;
    @FindBy (id = "validation-summary-id") private WebElement validationMessage;

    public ChangeNamePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeNamePage fillFirstName(String value) {
        FormDataHelper.enterText(firstNameInput, value);
        return this;
    }

    public ChangeNamePage fillLastName(String value) {
        FormDataHelper.enterText(lastNameInput, value);
        return this;
    }

    public <T extends Page>T clickSubmitButton(Class<T> clazz) {
        submitNameChangeButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public ProfilePage clickCancelAndReturnLink() {
        cancelAndReturnLink.click();
        return new UserProfilePage(driver);
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }
}