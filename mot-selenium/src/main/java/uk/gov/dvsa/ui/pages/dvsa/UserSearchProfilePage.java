package uk.gov.dvsa.ui.pages.dvsa;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.changedriverlicence.ChangeDrivingLicencePage;
import uk.gov.dvsa.ui.pages.profile.ChangeNamePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class UserSearchProfilePage extends ProfilePage {

    private static final String PAGE_TITLE = "User profile";
    public static final String PATH = "/user-admin/user-profile/%s";

    @FindBy(id = "manage-internal-roles") private WebElement selectManageRolesLink;
    @FindBy(css = "#display-name a") private WebElement changeNameLink;
    @FindBy(id = "person-driving-licence") private WebElement personDrivingLicence;
    @FindBy(id = "person-driving-licence-number") private WebElement personDrivingLicenceNumber;
    @FindBy(id = "person-driving-licence-region") private WebElement personDrivingLicenceRegion;
    @FindBy(id = "add-edit-driving-licence-link") private WebElement changeDrivingLicenceLink;
    @FindBy(id = "validation-message--success") private WebElement messageSuccess;
    @FindBy(id= "roles-and-associations-link") private WebElement rolesAndAssociationsLink;

    public UserSearchProfilePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    @Override
    public ManageRolesPage clickManageRolesLink(){
        selectManageRolesLink.click();
        return new ManageRolesPage(driver);
    }

    public RolesAndAssociationsPage clickRolesAndAssociationsLink() {
        rolesAndAssociationsLink.click();
        return new RolesAndAssociationsPage(driver);
    }
    
    public ChangeDrivingLicencePage clickChangeDrivingLicenceLink() {
        changeDrivingLicenceLink.click();
        return new ChangeDrivingLicencePage(driver);
    }

    @Override
    public ChangeNamePage clickChangeNameLink() {
        changeNameLink.click();
        return new ChangeNamePage(driver);
    }

    @Override
    public String getDrivingLicenceForPerson() {
        return personDrivingLicenceNumber.getText();
    }

    public boolean isSuccessMessageDisplayed(){
        return messageSuccess.isDisplayed();
    }

    @Override
    public String getMessageSuccess(){
        return messageSuccess.getText();
    }

    @Override
    public boolean drivingLicenceIsDisplayed() {
        return PageInteractionHelper.isElementDisplayed(personDrivingLicence);
    }

    public boolean addEditDrivingLicenceLinkExists() {
        return PageInteractionHelper.isElementDisplayed(changeDrivingLicenceLink);
    }

    @Override
    public boolean isRolesAndAssociationsLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(rolesAndAssociationsLink);
    }
}
