package uk.gov.dvsa.domain.shared.qualifications;

import java.util.HashMap;
import java.util.Map;

public class TesterQualifications {

    public enum TesterQualificationStatus {
        QUALIFIED ("QLFD"),
        REFRESHER_NEEDED ("RFSHN"),
        SUSPENDED ("SPND"),
        DEMO_TEST_NEEDED ("DMTN"),
        INITIAL_TRAINING_NEEDED ("ITRN"),
        UNKNOWN ("UNKN");

        String value;

        TesterQualificationStatus(String value) {
            this.value = value;
        }

        public String toString() {
            return this.value;
        }
    }

    private static final String GROUP_A = "A";
    private static final String GROUP_B = "B";

    private TesterQualificationStatus groupA;
    private TesterQualificationStatus groupB;

    public TesterQualifications(TesterQualificationStatus groupA, TesterQualificationStatus groupB) {
        this.groupA = groupA;
        this.groupB = groupB;
    }

    public Map<String, String> toJsonMap() {
        Map<String, String> qualifications = new HashMap<>();
        qualifications.put(GROUP_A, this.groupA.toString());
        qualifications.put(GROUP_B, this.groupB.toString());
        return qualifications;
    }
}
