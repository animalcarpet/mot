package uk.gov.dvsa.helper.enums;

public enum TesterStatus {
    QLFD("Qualified"),
    ITRN("Initial training needed"),
    SPND("Suspended"),
    DMTN("Demo Test Needed");

    private final String description;

    TesterStatus(String description) {
        this.description = description;
    }
}
