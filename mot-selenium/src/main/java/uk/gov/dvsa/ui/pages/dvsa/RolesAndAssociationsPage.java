package uk.gov.dvsa.ui.pages.dvsa;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.RemoveRolePage;
import uk.gov.dvsa.ui.pages.profile.UserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

import java.util.ArrayList;
import java.util.List;

public class RolesAndAssociationsPage extends Page{

    private static final String PAGE_TITLE = "Roles and associations";

    @FindBy(id = "trade-roles-table") private WebElement rolesTableElement;
    @FindBy(className = "history-go-back") private WebElement returnToUserProfileLink;
    @FindBy(id = "validation-message--success") private WebElement successMessage;
    @FindBy(id = "validation-message--failure") private WebElement failureMessage;
    @FindBy(id = "remove-role-link-0") private WebElement removeRoleLink;

    public RolesAndAssociationsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public List<String> getRoleValues() {
        List<String> roleValues = new ArrayList<>();
        roleValues.add(rolesTableElement.findElement(By.className("matched-records__value")).getText());
        roleValues.add(rolesTableElement.findElement(By.className("entity-header__tertiary")).getText());
        return roleValues;
    }

    public ProfilePage clickReturnToUserProfile() {
        returnToUserProfileLink.click();
        return new UserProfilePage(driver);
    }

    public RemoveRolePage removeRole() {
        removeRoleLink.click();
        return new RemoveRolePage(driver);
    }

    public String getSuccessMessage() {
        return successMessage.getText();
    }

    public String getFailureMessage() {
        return failureMessage.getText();
    }
}
