package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public final class EventHistory {
    private EventsHistoryPage historyPage;
    private AeDetails aeDetails;

    public void setHistoryPage(EventsHistoryPage historyPage, AeDetails aeDetails) {
        this.historyPage = historyPage;
        this.aeDetails = aeDetails;
    }

    public void containsEvent(String eventName) {
        if (historyPage != null) {
            assertThat(historyPage.getEventName(aeDetails.getIdAsString()), containsString(eventName));
        } else {
            throw new IllegalStateException("Event History Page is not loaded");
        }
    }
}
