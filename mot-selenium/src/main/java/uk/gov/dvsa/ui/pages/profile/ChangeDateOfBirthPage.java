package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeDateOfBirthPage extends Page {

    private static final String PAGE_TITLE = "Change date of birth";

    @FindBy(id = "date1-day") private WebElement dayInput;
    @FindBy (id = "date1-month") private WebElement monthInput;
    @FindBy (id = "date1-year") private WebElement yearInput;
    @FindBy (id = "submitDateOfBirthChange") private WebElement submitDOBChangeButton;
    @FindBy (id = "cancel-btn") private WebElement cancelAndReturnLink;
    @FindBy (css = ".validation-message") private WebElement validationMessage;

    public ChangeDateOfBirthPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeDateOfBirthPage fillDay(String value) {
        FormDataHelper.enterText(dayInput, value);
        return this;
    }

    public ChangeDateOfBirthPage fillMonth(String value) {
        FormDataHelper.enterText(monthInput, value);
        return this;
    }

    public ChangeDateOfBirthPage fillYear(String value) {
        FormDataHelper.enterText(yearInput, value);
        return this;
    }

    public <T extends Page>T clickSubmitButton(Class<T> clazz) {
        submitDOBChangeButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public ProfilePage clickCancelAndReturnLink() {
        cancelAndReturnLink.click();
        return new NewUserProfilePage(driver);
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }
}
