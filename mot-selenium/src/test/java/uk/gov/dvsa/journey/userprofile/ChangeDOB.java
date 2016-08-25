package uk.gov.dvsa.journey.userprofile;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import uk.gov.dvsa.ui.pages.profile.ChangeDateOfBirthPage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class ChangeDOB {
    private ProfilePage profilePage;

    public ChangeDOB(ProfilePage profilePage) {
        this.profilePage = profilePage;
    }
    public ProfilePage changeDateOfBirthTo(String dateOfBirth) {
        String pattern = "dd MMM yyyy";
        DateTime newDob = DateTime.parse(dateOfBirth, DateTimeFormat.forPattern(pattern));

        profilePage =  profilePage.clickChangeDOBLink()
                .fillDay(newDob.dayOfMonth().getAsString())
                .fillMonth(newDob.monthOfYear().getAsString())
                .fillYear(newDob.year().getAsString())
                .clickSubmitButton(NewUserProfilePage.class);

        return profilePage;
    }

    public String changeDOBwithInvalidValues(String day, String month, String year) {
        return profilePage.clickChangeDOBLink()
                .fillDay(day)
                .fillMonth(month)
                .fillYear(year)
                .clickSubmitButton(ChangeDateOfBirthPage.class).getValidationMessage();
    }
}
