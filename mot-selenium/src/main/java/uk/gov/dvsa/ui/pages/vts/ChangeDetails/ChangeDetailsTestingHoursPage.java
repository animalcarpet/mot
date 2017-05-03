package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.enums.DayFinder;
import uk.gov.dvsa.helper.enums.TimeFinder;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

public class ChangeDetailsTestingHoursPage extends Page {

    public static final String PATH = "/vehicle-testing-station/%s/opening-hours";
    private static final String PAGE_TITLE = "Change testing hours";

    @FindBy(id = "monday-time-text-open") private WebElement mondayOpeningTimeTextBox;
    @FindBy(id = "monday-am-radio-open") private WebElement mondayOpeningAmRadioButton;
    @FindBy(id = "monday-pm-radio-open") private WebElement mondayOpeningPmRadioButton;
    @FindBy(id = "monday-am-radio-close") private WebElement mondayClosingAmRadioButton;
    @FindBy(id = "monday-pm-radio-close") private WebElement mondayClosingPmRadioButton;
    @FindBy(id = "monday-time-text-close") private WebElement mondayClosingTimeTextBox;
    @FindBy(id = "mondayisClosed") private WebElement mondayClosedAllDayCheckBox;
    @FindBy(id = "tuesday-time-text-open") private WebElement tuesdayOpeningTimeTextBox;
    @FindBy(id = "tuesday-am-radio-open") private WebElement tuesdayOpeningAmRadioButton;
    @FindBy(id = "tuesday-am-radio-open") private WebElement tuesdayOpeningPmRadioButton;
    @FindBy(id = "tuesday-pm-radio-close") private WebElement tuesdayClosingAmRadioButton;
    @FindBy(id = "tuesday-pm-radio-close") private WebElement tuesdayClosingPmRadioButton;
    @FindBy(id = "tuesday-time-text-close") private WebElement tuesdayClosingTimeTextBox;
    @FindBy(id = "tuesdayisClosed") private WebElement tuesdayClosedAllDayCheckBox;
    @FindBy(id = "wednesday-time-text-open") private WebElement wednesdayOpeningTimeTextBox;
    @FindBy(id = "wednesday-am-radio-open") private WebElement wednesdayOpeningAmRadioButton;
    @FindBy(id = "wednesday-am-radio-open") private WebElement wednesdayOpeningPmRadioButton;
    @FindBy(id = "wednesday-pm-radio-close") private WebElement wednesdayClosingAmRadioButton;
    @FindBy(id = "wednesday-pm-radio-close") private WebElement wednesdayClosingPmRadioButton;
    @FindBy(id = "wednesday-time-text-close") private WebElement wednesdayClosingTimeTextBox;
    @FindBy(id = "wednesdayisClosed") private WebElement wednesdayClosedAllDayCheckBox;
    @FindBy(id = "thursday-time-text-open") private WebElement thursdayOpeningTimeTextBox;
    @FindBy(id = "thursday-am-radio-open") private WebElement thursdayOpeningAmRadioButton;
    @FindBy(id = "thursday-am-radio-open") private WebElement thursdayOpeningPmRadioButton;
    @FindBy(id = "thursday-pm-radio-close") private WebElement thursdayClosingAmRadioButton;
    @FindBy(id = "thursday-pm-radio-close") private WebElement thursdayClosingPmRadioButton;
    @FindBy(id = "thursday-time-text-close") private WebElement thursdayClosingTimeTextBox;
    @FindBy(id = "thursdayisClosed") private WebElement thursdayClosedAllDayCheckBox;
    @FindBy(id = "friday-time-text-open") private WebElement fridayOpeningTimeTextBox;
    @FindBy(id = "friday-am-radio-open") private WebElement fridayOpeningAmRadioButton;
    @FindBy(id = "friday-am-radio-open") private WebElement fridayOpeningPmRadioButton;
    @FindBy(id = "friday-pm-radio-close") private WebElement fridayClosingAmRadioButton;
    @FindBy(id = "friday-pm-radio-close") private WebElement fridayClosingPmRadioButton;
    @FindBy(id = "friday-time-text-close") private WebElement fridayClosingTimeTextBox;
    @FindBy(id = "fridayisClosed") private WebElement fridayClosedAllDayCheckBox;
    @FindBy(id = "saturday-time-text-open") private WebElement saturdayOpeningTimeTextBox;
    @FindBy(id = "saturday-am-radio-open") private WebElement saturdayOpeningAmRadioButton;
    @FindBy(id = "saturday-am-radio-open") private WebElement saturdayOpeningPmRadioButton;
    @FindBy(id = "saturday-pm-radio-close") private WebElement saturdayClosingAmRadioButton;
    @FindBy(id = "saturday-pm-radio-close") private WebElement saturdayClosingPmRadioButton;
    @FindBy(id = "saturday-time-text-close") private WebElement saturdayClosingTimeTextBox;
    @FindBy(id = "saturdayisClosed") private WebElement saturdayClosedAllDayCheckBox;
    @FindBy(id = "sunday-time-text-open") private WebElement sundayOpeningTimeTextBox;
    @FindBy(id = "sunday-am-radio-open") private WebElement sundayOpeningAmRadioButton;
    @FindBy(id = "sunday-am-radio-open") private WebElement sundayOpeningPmRadioButton;
    @FindBy(id = "sunday-pm-radio-close") private WebElement sundayClosingAmRadioButton;
    @FindBy(id = "sunday-pm-radio-close") private WebElement sundayClosingPmRadioButton;
    @FindBy(id = "sunday-time-text-close") private WebElement sundayClosingTimeTextBox;
    @FindBy(id = "sundayisClosed") private WebElement sundayClosedAllDayCheckBox;
    @FindBy(id = "update-opening-hours") private WebElement changeTestingHoursButton;

    public ChangeDetailsTestingHoursPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    private void enterTestingHours
            (Boolean isClosedAllDay, WebElement dayClosedAllDayCheckBox, WebElement dayOpeningTimeTextBox, WebElement dayClosingTimeTextBox,
             TimeFinder openingTime, TimeFinder closingTime) {
        FormDataHelper.enterText(dayOpeningTimeTextBox, openingTime.getName());
        FormDataHelper.enterText(dayClosingTimeTextBox, closingTime.getName());
        FormDataHelper.enterInputRadioButtonOrCheckbox(dayClosedAllDayCheckBox, isClosedAllDay);
    }

    private void checkOpeningClosingAmPmCheckboxes(TimeFinder openingTimeAmOrPm,
                                                   WebElement dayOpeningAmRadioButton, WebElement dayOpeningPmRadioButton,
                                                   TimeFinder closingTimeAmOrPm,
                                                   WebElement dayClosingAmRadioButton, WebElement dayClosingPmRadioButton) {
        if (openingTimeAmOrPm.getName().contains("am")) {
            FormDataHelper.enterInputRadioButtonOrCheckbox(dayOpeningAmRadioButton, true);
        } else
            FormDataHelper.enterInputRadioButtonOrCheckbox(dayOpeningPmRadioButton, true);

        if (closingTimeAmOrPm.getName().contains("pm")) {
            FormDataHelper.enterInputRadioButtonOrCheckbox(dayClosingPmRadioButton, true);
        } else
            FormDataHelper.enterInputRadioButtonOrCheckbox(dayClosingAmRadioButton, true);
    }

    public ChangeDetailsTestingHoursPage setTestingHoursForDay(DayFinder day, TimeFinder openingTime, TimeFinder openingTimeAmOrPm,
                                                                TimeFinder closingTime, TimeFinder closingTimeAmOrPm, Boolean isClosedAllDay) {
        switch (day) {
            case MONDAY:
                enterTestingHours(isClosedAllDay, mondayClosedAllDayCheckBox, mondayOpeningTimeTextBox, mondayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, mondayOpeningAmRadioButton, mondayOpeningPmRadioButton, closingTimeAmOrPm,
                        mondayClosingAmRadioButton, mondayClosingPmRadioButton);
                break;
            case TUESDAY:
                enterTestingHours(isClosedAllDay, tuesdayClosedAllDayCheckBox, tuesdayOpeningTimeTextBox, tuesdayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, tuesdayOpeningAmRadioButton, tuesdayOpeningPmRadioButton, closingTimeAmOrPm,
                        tuesdayClosingAmRadioButton, tuesdayClosingPmRadioButton);
                break;
            case WEDNESDAY:
                enterTestingHours(isClosedAllDay, wednesdayClosedAllDayCheckBox, wednesdayOpeningTimeTextBox, wednesdayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, wednesdayOpeningAmRadioButton, wednesdayOpeningPmRadioButton, closingTimeAmOrPm,
                        wednesdayClosingAmRadioButton, wednesdayClosingPmRadioButton);
                break;
            case THURSDAY:
                enterTestingHours(isClosedAllDay, thursdayClosedAllDayCheckBox, thursdayOpeningTimeTextBox, thursdayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, thursdayOpeningAmRadioButton, thursdayOpeningPmRadioButton, closingTimeAmOrPm,
                        thursdayClosingAmRadioButton, thursdayClosingPmRadioButton);
                break;
            case FRIDAY:
                enterTestingHours(isClosedAllDay, fridayClosedAllDayCheckBox, fridayOpeningTimeTextBox, fridayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, fridayOpeningAmRadioButton, fridayOpeningPmRadioButton, closingTimeAmOrPm,
                        fridayClosingAmRadioButton, fridayClosingPmRadioButton);
                break;
            case SATURDAY:
                enterTestingHours(isClosedAllDay, saturdayClosedAllDayCheckBox, saturdayOpeningTimeTextBox, saturdayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, saturdayOpeningAmRadioButton, saturdayOpeningPmRadioButton, closingTimeAmOrPm,
                        saturdayClosingAmRadioButton, saturdayClosingPmRadioButton);
                break;
            case SUNDAY:
                enterTestingHours(isClosedAllDay, sundayClosedAllDayCheckBox, sundayOpeningTimeTextBox, sundayClosingTimeTextBox, openingTime, closingTime);
                checkOpeningClosingAmPmCheckboxes(openingTimeAmOrPm, sundayOpeningAmRadioButton, sundayOpeningPmRadioButton, closingTimeAmOrPm,
                        sundayClosingAmRadioButton, sundayClosingPmRadioButton);
                break;
        }
        return this;
    }

    public VehicleTestingStationPage saveTestingHours() {
        changeTestingHoursButton.click();
        return new VehicleTestingStationPage(driver);
    }
}
