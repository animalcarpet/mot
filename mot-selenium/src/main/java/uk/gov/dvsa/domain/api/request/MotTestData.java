package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;
import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.Period;
import org.joda.time.format.DateTimeFormat;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.helper.ReasonForRejection;

import java.util.ArrayList;
import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)

    public class MotTestData {
    private int mileage;
    private TestOutcome outcome;
    private String issueDate;
    private String startDate;
    private String completedDate;
    private String expiryDate;
    private List<ReasonForRejection> rfrs = new ArrayList<>();

        public MotTestData(TestOutcome outcome, int mileage, DateTime issuedDate) {
            this(outcome, mileage, issuedDate, issuedDate,
                    issuedDate.plusMinutes((1)),
                    issuedDate.plus(Period.years(1).minusDays(1)));
        }


        public MotTestData(TestOutcome outcome, int mileage, DateTime issuedDate,
                           DateTime startDate, DateTime completedDate, DateTime expiryDate, List<ReasonForRejection> rfrList) {
            this(outcome, mileage, issuedDate, startDate, completedDate, expiryDate);
            rfrs = rfrList;
        }

        public MotTestData(TestOutcome outcome, int mileage, DateTime issuedDate,
                           DateTime startDate, DateTime completedDate, DateTime expiryDate) {
            this.mileage = mileage;
            this.outcome = outcome;
            this.issueDate = dateToString(issuedDate);
            this.startDate = dateTimeToString(startDate);
            this.completedDate = dateTimeToString(completedDate);
            this.expiryDate = dateToString(expiryDate);
        }

    private static String dateToString(DateTime date) {
        return date.toString(DateTimeFormat.forPattern("YYYY-MM-dd"));
    }

    private static String dateTimeToString(DateTime date) {
        return date.withZone(DateTimeZone.UTC)
                .toString(DateTimeFormat.forPattern("YYYY-MM-dd'T'HH:mm:ss'Z'"));
    }
}
