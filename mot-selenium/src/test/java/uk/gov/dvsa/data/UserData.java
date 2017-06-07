package uk.gov.dvsa.data;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.UserService;
import uk.gov.dvsa.domain.shared.qualifications.TesterQualifications;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

public class UserData extends UserService{

    public UserData() {}

    public User createUserWithoutRole() throws IOException {
        return createUserWithNoRole();
    }

    public User createTester(int siteId) throws IOException {
        return createUserAsTester(siteId, true);
    }

    public User createTesterWithTestGroup1(int siteId) throws IOException {

        TesterQualifications qualifications = new TesterQualifications(TesterQualifications.TesterQualificationStatus.QUALIFIED, TesterQualifications.TesterQualificationStatus.INITIAL_TRAINING_NEEDED);
        return createUserAsTester(siteId, false, true, qualifications);
    }

    public User createNon2FaTester(int siteId) throws IOException {
        return createUserAsTester(siteId, false);
    }

    public User createCentralAdminTeamUser() throws IOException {
        return super.createCentralAdminTeamUser();
    }

    public User createTester(int siteId, boolean claimAccount) throws IOException {
        return createUserAsTester(siteId, claimAccount, true);
    }


    public User createNon2FaTester(int siteId, boolean claimAccount) throws IOException {
        return createUserAsTester(siteId, claimAccount, false);
    }

    public User createCustomerServiceOfficer(boolean claimAccount) throws IOException {
        return createUserAsCsco(claimAccount);
    }

    public User createCSCO() throws IOException {
        return createUserAsCsco(false);
    }

    public User createCustomerServiceManager(boolean claimAccount) throws IOException {
        return createUserAsCsm(claimAccount);
    }

    public User createAedm(boolean claimAccount) throws IOException {
        int aeId = new AeData().createAeWithDefaultValues().getId();
        return createUserAsAedm(aeId, "def_ae", claimAccount);
    }

    public User createAedm(int aeId, String namePrefix, boolean claimAccount) throws IOException {
        return createUserAsAedm(aeId, namePrefix, claimAccount);
    }

    public User createVehicleExaminer(String namePrefix, boolean accountClaimRequired) throws IOException {
        return createUserAsVE(namePrefix, accountClaimRequired);
    }

    public User createSchemeUser(boolean accountClaimRequired) throws IOException {
        return createUserAsSchemeUser(accountClaimRequired);
    }

    public User createSchemeManagerUser(boolean accountClaimRequired) throws IOException {
        return createUserAsSchemeManagerUser(accountClaimRequired);
    }

    public User createAreaOfficeOne(String namePrefix) throws IOException {
        return createUserAsAreaOfficeOneUser(namePrefix);
    }

    public User createAreaOfficeTwo(String namePrefix) throws IOException {
        return createUserAsAreaOfficeTwo(namePrefix);
    }

    public User createSiteManager(int siteId, boolean accountClaimRequired) throws IOException {
        return createUserAsSiteManager(siteId, accountClaimRequired, true);
    }


    public User createNon2FaSiteManager(int siteId, boolean accountClaimRequired) throws IOException {
        return createUserAsSiteManager(siteId, accountClaimRequired, false);
    }

    public User createSiteAdmin(int siteId, boolean accountClaimRequired) throws IOException {
        return createUserAsSiteAdmin(siteId, accountClaimRequired, true);
    }

    public User createNon2FaSiteAdmin(int siteId, boolean accountClaimRequired) throws IOException {
        return createUserAsSiteAdmin(siteId, accountClaimRequired, false);
    }

    public User createDvlaOfficer(String diff) throws IOException {
        return createDvlaOfficerUser(diff);
    }

    public User createUserWithCustomData(String key, String value) throws IOException {
        Map<String, String> customDataMap = new HashMap<>();
        customDataMap.put(key, value);
        return createUserWithCustomValues(customDataMap);
    }
}
