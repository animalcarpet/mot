package uk.gov.dvsa.helper.enums;

public enum DayFinder {
    MONDAY ("Monday"),
    TUESDAY ("Tuesday"),
    WEDNESDAY ("Wednesday"),
    THURSDAY ("Thursday"),
    FRIDAY ("Friday"),
    SATURDAY ("Saturday"),
    SUNDAY ("Sunday");

    private String day;
    DayFinder(String days) {
        this.day = days;
    }

    public String getName(){
        return day;
    }
}
