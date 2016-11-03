package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AddressPage extends Page {

    private static final String PAGE_TITLE = "Your contact details";

    @FindBy(id = "address1") private WebElement homeAddressLineOne;

    @FindBy(id = "address2") private WebElement homeAddressLineTwo;

    @FindBy(id = "address3") private WebElement homeAddressLineThree;

    @FindBy(id = "townOrCity") private WebElement townCity;

    @FindBy(id = "postcode") private WebElement postcode;

    @FindBy(id = "continue") private WebElement continueToNextPage;

    @FindBy(id = "phone") private WebElement telephoneNumber;

    public AddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public SecurityQuestionOnePage clickContinue()
    {
        continueToNextPage.click();
        return new SecurityQuestionOnePage(driver);
    }

    public SecurityQuestionOnePage enterAddressAndSubmitExpectingFirstSecurityQuestionPage(String addressLine1, String addressLine2, String addressLine3, String town,
                                                     String postCode, String telephone) {
        FormDataHelper.enterText(homeAddressLineOne, addressLine1);
        FormDataHelper.enterText(homeAddressLineTwo, addressLine2);
        FormDataHelper.enterText(homeAddressLineThree, addressLine3);
        FormDataHelper.enterText(townCity, town);
        FormDataHelper.enterText(postcode, postCode);
        FormDataHelper.enterText(telephoneNumber, telephone);
        continueToNextPage.click();
        return new SecurityQuestionOnePage(driver);
    }

    public AddressPage enterAddressandTelephone()
    {
        FormDataHelper.enterText(homeAddressLineOne, ContactDetailsHelper.getAddressLine1());
        FormDataHelper.enterText(homeAddressLineTwo, ContactDetailsHelper.getAddressLine2());
        FormDataHelper.enterText(homeAddressLineThree, ContactDetailsHelper.getAddressLine3());
        FormDataHelper.enterText(townCity, ContactDetailsHelper.getCity());
        FormDataHelper.enterText(postcode, ContactDetailsHelper.getPostCode());
        FormDataHelper.enterText(telephoneNumber, ContactDetailsHelper.getPhoneNumber());

        return this;
    }


}
