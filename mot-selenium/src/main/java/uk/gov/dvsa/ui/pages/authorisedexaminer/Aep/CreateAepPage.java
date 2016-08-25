package uk.gov.dvsa.ui.pages.authorisedexaminer.Aep;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.PersonDetails;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class CreateAepPage extends Page {

    @FindBy(id = "first-name") private WebElement firstName;
    @FindBy(id = "middle-name") private WebElement middleName;
    @FindBy(id = "last-name") private WebElement lastName;
    @FindBy(id = "address-line1") private WebElement addressLine1;
    @FindBy(id = "address-line2") private WebElement addressLine2;
    @FindBy(id = "address-line3") private WebElement addressLine3;
    @FindBy(id = "postcode") private WebElement postcode;
    @FindBy(id = "dob-day") private WebElement dateOfBirthDay;
    @FindBy(id = "dob-month") private WebElement dateOfBirthMonth;
    @FindBy(id = "dob-year") private WebElement dateOfBirthYear;
    @FindBy(id = "town") private WebElement town;
    @FindBy(id = "confirm-button") private WebElement submit;

    private static final String pageTitle = "Add a principal";
    public static final String PATH = "/authorised-examiner/%s";

    public CreateAepPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), pageTitle);
    }

    public CreateAepPage changeFirstName(String value) {
        FormDataHelper.enterText(firstName, value);
        return this;
    }

    public CreateAepPage changeLastName(String value) {
        FormDataHelper.enterText(lastName, value);
        return this;
    }

    public CreateAepPage changeDateOfBirthDay(String value) {
        FormDataHelper.enterText(dateOfBirthDay, value);
        return this;
    }

    public CreateAepPage changeDateOfBirthMonth(String value) {
        FormDataHelper.enterText(dateOfBirthMonth, value);
        return this;
    }

    public CreateAepPage changeDateOfBirthYear(String value) {
        FormDataHelper.enterText(dateOfBirthYear, value);
        return this;
    }

    public CreateAepPage changeAddressLine1(String value) {
        FormDataHelper.enterText(addressLine1, value);
        return this;
    }

    public CreateAepPage changeTown(String value) {
        FormDataHelper.enterText(town, value);
        return this;
    }

    public CreateAepPage changePostcode(String value) {
        FormDataHelper.enterText(postcode, value);
        return this;
    }

    public String getFirstName() {
        return firstName.getText();
    }

    public String getMiddleName() {
        return middleName.getText();
    }

    public String getLastName() {return lastName.getText(); }

    public String getAddressLine1() {
        return addressLine1.getText();
    }

    public String getAddressLine2() {
        return addressLine2.getText();
    }

    public String getAddressLine3() {
        return addressLine3.getText();
    }

    public String getPostcode() {
        return postcode.getText();
    }

    public String getDateOfBirthDay() {
        return dateOfBirthDay.getText();
    }

    public String getDateOfBirthMonth() {
        return dateOfBirthMonth.getText();
    }

    public String getDateOfBirthYear() {
        return dateOfBirthYear.getText();
    }

    public String getTown() {
        return town.getText();
    }

    public void fillInForm(PersonDetails aep) {
        this.changeFirstName(aep.getFirstName())
                .changeLastName(aep.getLastName())
                .changeDateOfBirthDay(String.valueOf(aep.getDateOfBirthDay()))
                .changeDateOfBirthMonth(String.valueOf(aep.getDateOfBirthMonth()))
                .changeDateOfBirthYear(String.valueOf(aep.getDateOfBirthYear()))
                .changePostcode(aep.getAddress().getPostcode())
                .changeAddressLine1(aep.getAddress().getLine1())
                .changeTown(aep.getAddress().getTown());
    }

    public ReviewCreateAepPage reviewPrincipal() {
        submit.click();
        return new ReviewCreateAepPage(driver);
    }
}

