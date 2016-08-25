package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReasonToCancelTestPage extends Page {
    @FindBy(id = "reasonForCancel13")
    private WebElement reasonAccidentOrIllness;
    @FindBy(id = "reasonForCancel25")
    private WebElement reasonAbortedByVE;
    @FindBy(id = "reasonForCancel28")
    private WebElement reasonVehicleRegisteredError;
    @FindBy(id = "reasonForCancel12")
    private WebElement reasonTestEquipmentIssue;
    @FindBy(id = "reasonForCancel5")
    private WebElement reasonVTSincident;
    @FindBy(id = "reasonForCancel6")
    private WebElement reasonIncorrectLocation;
    @FindBy(id = "reasonForCancel21")
    private WebElement reasonDangerousOrCauseDamage;
    private By enterYourPinField = By.id("oneTimePassword");
    @FindBy(id = "declarationStatement")
    private WebElement declarationElement;
    @FindBy(id = "mot_test_cancel_confirm")
    private WebElement confirmAndCancelTestButton;
    @FindBy(id = "returnToMotTest")
    private WebElement returnToMotTest;
    @FindBy(id = "cancelComment")
    private WebElement cancelComment;

    private static final String PAGE_TITLE = "Reasons to cancel test";
    public static final String PATH = "mot-test/%s/cancel";
    private static final String PIN = "123456";

    public ReasonToCancelTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void enterReason(CancelTestReason reason) {
        selectReason(reason);
        if (reason.equals(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE)) {
            enterCancelComment(reason.getDescription());
            if (isOneTimePasswordBoxDisplayed()) {
                enterUserPin(driver.getCurrentUser());
            }
        }
    }

    public void enterReason2FaActiveUser(CancelTestReason reason) {
        selectReason(reason);
        if (reason.equals(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE)) {
            enterCancelComment(reason.getDescription());
        }
    }

    public void clickConfirmAndCancelTest() {
        confirmAndCancelTestButton.click();
    }

    private void enterUserPin(User currentUser) {
        driver.findElement(enterYourPinField).sendKeys(currentUser.getOneTimePasswordPin());
    }

    public void selectReason(CancelTestReason reason) {
        switch (reason) {
            case ACCIDENT_OR_ILLNESS:
                selectReasonAccidentOrIllness();
                break;
            case ABORTED_BY_VE:
                selectReasonAbortedByVE();
                break;
            case VEHICLE_REGISTERED_IN_ERROR:
                selectReasonVehicleRegisteredError();
                break;
            case TEST_EQUIPMENT_ISSUE:
                selectReasonTestEquipmentIssue();
                break;
            case VTS_INCIDENT:
                selectReasonVTSincident();
                break;
            case INCORRECT_LOCATION:
                selectReasonIncorrectLocation();
                break;
            case DANGEROUS_OR_CAUSE_DAMAGE:
                selectReasonDangerousOrCauseDamage();
                break;
            default:
                break;
        }
    }

    public boolean isOneTimePasswordBoxDisplayed() {
        return PageInteractionHelper.isElementDisplayed((enterYourPinField));
    }

    private void selectReasonAccidentOrIllness() {
        reasonAccidentOrIllness.click();
    }

    private void selectReasonAbortedByVE() {
        reasonAbortedByVE.click();
    }

    private void selectReasonVehicleRegisteredError() {
        reasonVehicleRegisteredError.click();
    }

    private void selectReasonTestEquipmentIssue() {
        reasonTestEquipmentIssue.click();
    }

    private void selectReasonIncorrectLocation() {
        reasonIncorrectLocation.click();
    }

    private void selectReasonVTSincident() {
        reasonVTSincident.click();
    }

    private void selectReasonDangerousOrCauseDamage() {
        reasonDangerousOrCauseDamage.click();
    }

    private void enterCancelComment(String comment) {
        cancelComment.sendKeys(comment);
    }

    public boolean isDeclarationTextDisplayed() {
        return declarationElement.isDisplayed();
    }

    public String getDeclarationText() {
        return declarationElement.getText();
    }
}
