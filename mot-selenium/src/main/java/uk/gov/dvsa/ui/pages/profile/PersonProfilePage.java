package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.ChangePasswordFromProfilePage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;

public class PersonProfilePage extends ProfilePage {

    public static final String PATH = "/your-profile";
    private static final String PAGE_TITLE = "Your profile";

    @FindBy(id = "full-address") private WebElement addressField;
    @FindBy(css = "#date-of-birth a") protected WebElement changeDOBLink;
    @FindBy(css = "#display-name a") private WebElement changeNameLink;
    @FindBy(id = "email-address") private WebElement emailAddressField;
    @FindBy(id = "tester-qualification-status") private WebElement qualificationStatus;
    @FindBy(id = "change-password") private WebElement changePasswordLink;
    @FindBy(id = "validation-message--success") private WebElement messageSuccess;
    @FindBy(id="roles-and-associations-link") private WebElement rolesAndAssociationsLink;
    @FindBy(id = "account_security") private WebElement accountSecurity;
    @FindBy(id = "security-card-order") private WebElement orderSecurityCardLink;


    public PersonProfilePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean verifyPostCodeIsChanged(String postcode) {
        return addressField.getText().contains(postcode);
    }

    public boolean verifyEmailIsChanged(String email) {
        return emailAddressField.getText().equals(email);
    }

    @Override
    public boolean isAccountSecuritySectionDisplayed() {
        return accountSecurity.isDisplayed();
    }

    @Override
    public boolean isTesterQualificationStatusDisplayed() {
        return qualificationStatus.isDisplayed();
    }

    @Override
    public boolean isChangeDOBLinkIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeDOBLink);
    }

    @Override
    public boolean isChangeNameLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeNameLink);
    }

    @Override
    public boolean isOrderSecurityCardDisplayed() { return qualificationStatus.isDisplayed(); }

    public ChangePasswordFromProfilePage clickChangePasswordLink() {
        changePasswordLink.click();
        return new ChangePasswordFromProfilePage(driver);
    }

    @Override
    public RolesAndAssociationsPage clickRolesAndAssociationsLink() {
        rolesAndAssociationsLink.click();
        return new RolesAndAssociationsPage(driver);
    }

    public boolean isSuccessMessageDisplayed(){
        return messageSuccess.isDisplayed();
    }

    public String getMessageSuccess(){
       return messageSuccess.getText();
    }

    @Override
    public boolean isRolesAndAssociationsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(rolesAndAssociationsLink);
    }
}
