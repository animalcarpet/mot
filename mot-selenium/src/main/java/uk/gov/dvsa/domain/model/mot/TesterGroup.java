package uk.gov.dvsa.domain.model.mot;

public enum TesterGroup {
    GROUP_1("A", "Vehicle classes 1 and 2"), GROUP_2("B", "Vehicle classes 3 to 7");

    public final String group;
    public final String description;

    private TesterGroup(String group, String description) {
        this.group = group;
        this.description = description;
    }
}
