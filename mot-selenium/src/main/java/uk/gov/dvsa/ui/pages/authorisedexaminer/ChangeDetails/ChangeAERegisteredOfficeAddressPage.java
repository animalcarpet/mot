package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ConfirmChangeDetails.ConfirmChangeAERegisteredOfficeAddressPage;

public class ChangeAERegisteredOfficeAddressPage extends Page {
    public static final String PAGE_TITLE = "Change registered office address";
    public static final String PATH = "/vehicle-testing-station/%s/registered-address/change";

    @FindBy(id = "submitUpdate") private WebElement submitButton;
    @FindBy (id = "aeAddressLine1") private WebElement contactDetailsAddressLine1;
    @FindBy (id = "town") private WebElement contactDetailsTown;
    @FindBy (id = "postcode") private WebElement contactDetailsPostcode;

    public ChangeAERegisteredOfficeAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ConfirmChangeAERegisteredOfficeAddressPage clickConfirmationSubmitButton() {
        submitButton.click();
        return new ConfirmChangeAERegisteredOfficeAddressPage(driver);
    }

    public ChangeAERegisteredOfficeAddressPage changeFirstAddressLine(String street) {
        FormDataHelper.enterText(contactDetailsAddressLine1, street);
        return this;
    }

    public ChangeAERegisteredOfficeAddressPage changeTown(String street) {
        FormDataHelper.enterText(contactDetailsTown, street);
        return this;
    }

    public ChangeAERegisteredOfficeAddressPage changePostcode(String street) {
        FormDataHelper.enterText(contactDetailsPostcode, street);
        return this;
    }
}
