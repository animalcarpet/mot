package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ConfirmChangeDetails.ConfirmChangeAECorrespondenceAddressPage;

public class ChangeAECorrespondenceAddressPage extends Page {
    public static final String PAGE_TITLE = "Change correspondence address";
    public static final String PATH = "/authorised-examiner/%s/correspondence-address/change";

    @FindBy(id = "submitUpdate") private WebElement submitButton;
    @FindBy (id = "aeAddressLine1") private WebElement contactDetailsAddressLine1;
    @FindBy (id = "town") private WebElement contactDetailsTown;
    @FindBy (id = "postcode") private WebElement contactDetailsPostconde;

    public ChangeAECorrespondenceAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ConfirmChangeAECorrespondenceAddressPage clickConfirmationSubmitButton() {
        submitButton.click();
        return new ConfirmChangeAECorrespondenceAddressPage(driver);
    }

    public ChangeAECorrespondenceAddressPage changeFirstAddressLine(String street) {
        FormDataHelper.enterText(contactDetailsAddressLine1, street);
        return this;
    }

    public ChangeAECorrespondenceAddressPage changeTown(String street) {
        FormDataHelper.enterText(contactDetailsTown, street);
        return this;
    }

    public ChangeAECorrespondenceAddressPage changePostcode(String street) {
        FormDataHelper.enterText(contactDetailsPostconde, street);
        return this;
    }
}