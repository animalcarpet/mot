package uk.gov.dvsa.domain.model;

public enum SecurityQuestion {
    FIRST_KISS("Who was your first kiss?", "1"),
    FIRST_SCHOOL_TRIP("Where did you go on your first school trip?", "6");

    public final String text;
    public final String optionValue;

    SecurityQuestion(String text, String optionValue) {
        this.text = text;
        this.optionValue = optionValue;
    }
}
