package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EnterSecurityCardAddressPage extends Page {
    private static final String PAGE_TITLE = "Order a security card";
    public static final String PATH= "/security-card-order/address";

    @FindBy (id = "address1") private WebElement address1Field;
    @FindBy (id = "address2") private WebElement address2Field;
    @FindBy (id = "address3") private WebElement address3Field;
    @FindBy (id = "townOrCity") private WebElement townOrCity;
    @FindBy (id = "postcode") private WebElement postcode;
    @FindBy (id = "addressChoice0") private WebElement homeAddressRadioBox;
    @FindBy (id = "addressChoice1") private WebElement firstVtsAddressRadioBox;
    @FindBy (id = "addressChoiceCustom") private WebElement customAddressRadioBox;
    private By submitButton = By.id("submit");

    public EnterSecurityCardAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }


    public <T extends Page> T submitAddress(Class<T> klass){
        driver.findElement(submitButton).click();
        return MotPageFactory.newPage(driver, klass);
    }

    public EnterSecurityCardAddressPage chooseCustomAddress() {
        FormDataHelper.selectInputBox(customAddressRadioBox);
        return this;
    }

    public EnterSecurityCardAddressPage fillAddressLine1(String value) {
        FormDataHelper.enterText(address1Field, value);
        return this;
    }

    public EnterSecurityCardAddressPage fillAddressLine2(String value) {
        FormDataHelper.enterText(address2Field, value);
        return this;
    }

    public EnterSecurityCardAddressPage fillAddressLine3(String value) {
        FormDataHelper.enterText(address3Field, value);
        return this;
    }

    public EnterSecurityCardAddressPage fillTownOrCity(String value) {
        FormDataHelper.enterText(townOrCity, value);
        return this;
    }

    public EnterSecurityCardAddressPage fillPostcode(String value) {
        FormDataHelper.enterText(postcode, value);
        return this;
    }

    public EnterSecurityCardAddressPage chooseVTSAddress() {
        FormDataHelper.selectInputBox(firstVtsAddressRadioBox);
        return this;
    }

    public EnterSecurityCardAddressPage chooseHomeAddress() {
        FormDataHelper.selectInputBox(homeAddressRadioBox);
        return this;
    }
}
