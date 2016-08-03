package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;

public class ChangeDetailsPage extends Page {
    public static final String PATH = "/profile/edit";
    private static final String PAGE_TITLE = "Change details";

    @FindBy (id = "postcode") private WebElement postCodeField;
    @FindBy (id = "email") private WebElement emailField;
    @FindBy (id = "emailConfirmation") private WebElement emailConfirmationField;
    @FindBy (id = "update-profile") private WebElement updateButton;

    public ChangeDetailsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeDetailsPage editPostCode(String postcode) {
        FormDataHelper.enterText(postCodeField, postcode);

        return this;
    }

    public ChangeDetailsPage editEmailAndConfirmEmail(String email, String confirmEmail) {
        FormDataHelper.enterText(emailField, email);
        FormDataHelper.enterText(emailConfirmationField, confirmEmail);

        return this;
    }

    public PersonProfilePage update() {
        updateButton.click();
        return new PersonProfilePage(driver);
    }
}
