package uk.gov.dvsa.ui.views;

import org.testng.annotations.Test;
import ru.yandex.qatools.allure.annotations.Features;
import ru.yandex.qatools.allure.annotations.Issue;
import ru.yandex.qatools.allure.annotations.Issues;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.events.HistoryType;

import java.io.IOException;

public class EventHistoryViewTests extends DslTest {

    @Issues({@Issue("VM-5153"), @Issue("VM-5154")})
    @Features("List Events for Selected Entity - Search & Filter")
    @Test(groups = {"BVT"})
    public void viewEventHistory() throws IOException {
        step("Given I create Test AE");
        AeDetails aeDetails = aeData.createNewAe("Test AE", 1);

        step("When I view Test AE event history as Ao1");
        motUI.showEventHistoryFor(HistoryType.AE, motApi.user.createAreaOfficeOne("ao1"), aeDetails);

        step("Then I should see a Create event");
        motUI.eventHistory.containsEvent("DVSA Administrator Create AE");
    }
}
