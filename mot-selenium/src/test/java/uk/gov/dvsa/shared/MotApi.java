package uk.gov.dvsa.shared;

import org.joda.time.DateTime;

import uk.gov.dvsa.data.UserData;
import uk.gov.dvsa.data.VehicleData;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.MotTest;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.service.MotTestService;
import uk.gov.dvsa.domain.service.NominationService;
import uk.gov.dvsa.domain.service.SessionManager;
import uk.gov.dvsa.helper.ReasonForRejection;

import java.io.IOException;
import java.util.List;

public class MotApi extends MotTestService{
    private VehicleData vehicleData = new VehicleData();
    public final UserData user = new UserData();

    public final NominationService nominations = new NominationService();

    public MotTest createTest(User requestor, int siteId, Vehicle vehicle, TestOutcome outcome,
                              int mileage, DateTime issuedDate) throws IOException {
        return createMotTest(requestor, siteId, vehicle, outcome, mileage, issuedDate);
    }

    public MotTest createTestWithRfr(User requestor, int siteId, Vehicle vehicle, TestOutcome outcome,
                                 int mileage, DateTime issuedDate, List<ReasonForRejection> rfrs) throws IOException {
        return createMotTestWithRfr(requestor, siteId, vehicle, outcome, mileage, issuedDate, rfrs);
    }

    public void createMultipleSession(String username, String password, int numberOfSessions) throws IOException {
        for (int i = 1; i < numberOfSessions; i++) {
            SessionManager.createSession(username, password);
        }
    }

    public MotTest createFailedTest(User user, int siteId) throws IOException {
        return createTest(user, siteId, vehicleData.getNewVehicle(user), TestOutcome.FAILED, 123456, DateTime.now());
    }

    public MotTest createPassedTest(User user, int siteId) throws IOException {
        return createTest(user, siteId, vehicleData.getNewVehicle(user), TestOutcome.PASSED, 123456, DateTime.now());
    }

    public MotTest createFailedTestForVehicle(User user, int siteId, Vehicle vehicle) throws IOException {
        return createTest(user, siteId, vehicle, TestOutcome.FAILED, 123456, DateTime.now());
    }

    public MotTest createPassedTestForVehicle(User user, int siteId, Vehicle vehicle) throws IOException {
        return createTest(user, siteId, vehicle, TestOutcome.PASSED, 123456, DateTime.now());
    }

    public void generateGdsSurveyReport(User user) throws IOException {
        generateSurveyReport(user);
    }
}
