package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.events.CreateNewEventPage;
import uk.gov.dvsa.ui.pages.events.CreateNewEventPageTwo;
import uk.gov.dvsa.ui.pages.events.NewEventSummaryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ManualEventTests extends DslTest {

    private User dvsaUser;
    private AeDetails ae;
    private int aeId;

    @BeforeClass(alwaysRun = true)
    private void setupTestData() throws IOException {
        dvsaUser = userData.createAreaOfficeOne("AreaOffice1User");
        ae = aeData.createNewAe("NewAuthorisedExaminer", 10);
        aeId = ae.getId();
    }

    @Test(groups = {"Regression"}, description = "VM-11545")
    public void recordManualEventSuccessfully() throws IOException, URISyntaxException {

        //Given I am on the Events History Page
        EventsHistoryPage eventsHistoryPage = pageNavigator.goToPageAsAuthorisedExaminer(dvsaUser, EventsHistoryPage.class, EventsHistoryPage.AE_PATH, aeId);

        //And I click on the 'Record new event' link
        CreateNewEventPage createNewEventPage = eventsHistoryPage.clickRecordNewEvent();

        //And I submit a new event
        CreateNewEventPageTwo createNewEventPageTwo = createNewEventPage.submitNewEvent();

        //Then I should also submit an event outcome based on the event chosen and enter a note
        NewEventSummaryPage newEventSummaryPage = createNewEventPageTwo.submitEventOutcomeAndDescription("Test");

        //So that I can successfully record a new event
        newEventSummaryPage.clickRecordNewEvent();
        assertThat(newEventSummaryPage.isEventRecordedSuccessfully(), is(true));
    }
}







