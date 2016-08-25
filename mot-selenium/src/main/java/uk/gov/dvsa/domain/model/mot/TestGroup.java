package uk.gov.dvsa.domain.model.mot;

public enum TestGroup {
    GROUP_1(1, "12"), GROUP_2(2, "3+");

    public final static TestGroup ALL = null;

    public final int group;
    public final String description;

    private TestGroup(int group, String description) {
        this.group = group;
        this.description = description;
    }
}
