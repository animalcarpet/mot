package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.ConfirmChangeDetails.ConfirmChangeDetailsAddressPage;

public class ChangeDetailsAddressPage extends Page {
    public static final String PAGE_TITLE = "Change address";
    public static final String PATH = "/vehicle-testing-station/%s/address/change";

    @FindBy(id = "submitUpdate") private WebElement submitButton;
    @FindBy (id = "vtsAddressLine1") private WebElement contactDetailsAddressLine1;
    @FindBy (id = "town") private WebElement contactDetailsTown;
    @FindBy (id = "postcode") private WebElement contactDetailsPostcode;

    public ChangeDetailsAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ConfirmChangeDetailsAddressPage clickConfirmationSubmitButton() {
        submitButton.click();
        return new ConfirmChangeDetailsAddressPage(driver);
    }

    public ChangeDetailsAddressPage changeFirstAddressLine(String street) {
        FormDataHelper.enterText(contactDetailsAddressLine1, street);
        return this;
    }

    public ChangeDetailsAddressPage changeTown(String street) {
        FormDataHelper.enterText(contactDetailsTown, street);
        return this;
    }

    public ChangeDetailsAddressPage changePostcode(String street) {
        FormDataHelper.enterText(contactDetailsPostcode, street);
        return this;
    }
}
