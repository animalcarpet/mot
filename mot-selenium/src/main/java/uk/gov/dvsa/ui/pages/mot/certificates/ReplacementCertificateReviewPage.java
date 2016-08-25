package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReplacementCertificateReviewPage extends Page {
    private static String PAGE_TITLE = "Replacement certificate review";

    @FindBy(id = "cancelMotTest") private WebElement cancelAndReturnToVehicle;
    @FindBy(id = "testStatus") private WebElement testStatus;
    @FindBy(id = "expiryDate") private WebElement expiryDate;
    @FindBy(id = "registrationNumber") private WebElement registrationNumber;
    @FindBy(id = "vinChassisNumber") private WebElement vinNumber;
    @FindBy(id = "testClass") private WebElement vehicleClass;
    @FindBy(id = "make") private WebElement vehicleMake;
    @FindBy(id = "motTestNumber") private WebElement motTestNumber;
    @FindBy(id = "model") private WebElement vehicleModel;
    @FindBy(id = "colour") private WebElement vehicleColours;
    @FindBy(id = "brakeResults") private WebElement brakeResults;
    @FindBy(id = "prses") private WebElement prsRecorded;
    @FindBy(id = "advisoryText") private WebElement advisoriesRecorded;
    @FindBy(id = "odometerReading") private WebElement odometerReading;
    @FindBy(id = "confirm_and_print") private WebElement printReplacementCertificate;
    @FindBy(id = "oneTimePassword") private WebElement oneTimePassword;
    @FindBy(id = "otpErrorMessage") private WebElement otpErrorMessage;
    @FindBy(id = "otpErrorMessageDescription") private WebElement otpErrorMessageDescription;
    @FindBy(xpath = "//a[text()='Report a faulty card']") private WebElement reportFaultyCardLink;
    @FindBy(id = "vtsNameAndAddress") private WebElement vtsName;
    @FindBy(id = "reasonForDifferentTester") private WebElement reasonForDifferentTester;
    @FindBy(id = "confirm_and_print") private WebElement confirmAndPrint;
    @FindBy(id = "declarationStatement") private WebElement declarationElement;

    public ReplacementCertificateReviewPage (MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String testStatus() {
        return testStatus.getText();
    }

    public String expiryDate() {
        return expiryDate.getText();
    }

    public String registrationNumber() {
        return registrationNumber.getText();
    }

    public String vinNumber() {
        return vinNumber.getText();
    }

    public String vehicleClass() {
        return vehicleClass.getText();
    }

    public String vehicleMake() {
        return vehicleMake.getText();
    }

    public String vehicleModel() {
        return vehicleModel.getText();
    }

    public String vehicleColours() {
        return vehicleColours.getText();
    }

    public String odometerReading() {
        return odometerReading.getText();
    }

    public String getVtsName() {
        return vtsName.getText();
    }

    public String getMotTestNumber() {
        return motTestNumber.getText();
    }

    public boolean isDeclarationTextDisplayed() {
        return declarationElement.isDisplayed();
    }

    public String getDeclarationText() {
        return declarationElement.getText();
    }

    public <T extends Page> T confirmAndPrint(Class<T> clazz) {
        confirmAndPrint.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public boolean isPinBoxDisplayed() {
        return PageInteractionHelper.isElementDisplayed(oneTimePassword);
    }
}
