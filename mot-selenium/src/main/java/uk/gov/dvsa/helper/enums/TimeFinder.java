package uk.gov.dvsa.helper.enums;

public enum TimeFinder {
    ONE_O_CLOCK ("01:00"),
    TWO_O_CLOCK ("02:00"),
    THREE_O_CLOCK ("03:00"),
    FOUR_O_CLOCK("04:00"),
    FIVE_O_CLOCK ("05:00"),
    SIX_O_CLOCK ("06:00"),
    SEVEN_O_CLOCK("07:00"),
    EIGHT_O_CLOCK ("08:00"),
    NINE_O_CLOCK ("09:00"),
    TEN_O_CLOCK ("10:00"),
    ELEVEN_O_CLOCK ("11:00"),
    TWELVE_O_CLOCK ("12:00"),

    AM ("am"),
    PM ("pm");

    private String time;
    TimeFinder(String hours) {
        this.time = hours;
    }

    public String getName(){
        return time;
    }
}
