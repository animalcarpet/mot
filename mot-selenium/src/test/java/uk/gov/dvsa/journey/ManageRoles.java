package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.dvsa.ManageRolesPage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.util.List;

public class ManageRoles {

    private PageNavigator pageNavigator;
    private static final String ERROR_MESSAGE_ON_REMOVE_ROLE_PAGE = "You currently have a vehicle registered for test " +
            "or retest. This must be completed or aborted before you can remove this role.";

    public ManageRoles(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public void addRole(String role) {
        ManageRolesPage manageRolesPage = getManageRolesPage();
        switch (role) {
            case "AO2":
                manageRolesPage.clickAddRoleAO2User();
                break;
            case "VE":
                manageRolesPage.clickAddRoleVELink();
                break;
            default:
                break;
        }
        manageRolesPage.clickAddRoleButton();
    }

    public void removeRole(String role) {
        ManageRolesPage manageRolesPage = getManageRolesPage();
        switch (role) {
            case "AO2":
                manageRolesPage.clickRemoveRoleAO2Link();
                break;
            case "VE":
                manageRolesPage.clickRemoveRoleVELink();
                break;
            default:
                break;
        }
        manageRolesPage.clickRemoveRoleButton();
    }

    public String confirmRemoveRoleAction() {
        return getManageRolesPage().getNotificationText();
    }

    public boolean isRolesTableContainsValidTesterData() {
        RolesAndAssociationsPage rolesAndAssociationsPage = getRolesAndAssociationsPage();
        List<String> roleValues = rolesAndAssociationsPage.getRoleValues();
        boolean role = roleValues.get(0).equals("Tester");
        boolean vtsAddress = roleValues.get(1).contains("Flat Test_Site Lord House, Boston, BT2 4RR");
        return vtsAddress && role;
    }

    public boolean isErrorMessageDisplayedOnRolesAndAssociationsPage() {
        return getRolesAndAssociationsPage().getFailureMessage().equals(ERROR_MESSAGE_ON_REMOVE_ROLE_PAGE);
    }

    public boolean isSuccessMessageDisplayedOnRolesAndAssociationsPage() {
        return getRolesAndAssociationsPage().getSuccessMessage().equals("Role removed successfully.");
    }

    public boolean isUserAssignedToVts(User user) {
        VehicleTestingStationPage vehicleTestingStationPage = new VehicleTestingStationPage(pageNavigator.getDriver());
        return vehicleTestingStationPage.isTesterDisplayed(user.getId());
    }

    private ManageRolesPage getManageRolesPage() {
        return new ManageRolesPage(pageNavigator.getDriver());
    }

    private RolesAndAssociationsPage getRolesAndAssociationsPage() {
        return new RolesAndAssociationsPage(pageNavigator.getDriver());
    }
}
