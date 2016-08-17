package uk.gov.dvsa.domain.model.mot;

public class Defect {

    public enum DefectType {Failure, PRS, Advisory};

    private String[] categoryPath;
    private String defectName;
    private DefectType defectType;
    private String addOrRemoveName;

    public Defect(String[] categoryPath, String defectName, DefectType defectType, String addOrRemoveName) {
        this.categoryPath = categoryPath;
        this.defectName = defectName;
        this.defectType = defectType;
        this.addOrRemoveName = addOrRemoveName;
    }

    public String[] getCategoryPath() {
        return categoryPath;
    }

    public String getDefectName() {
        return defectName;
    }

    public String getDefectType() {
        switch(defectType) {
            case Advisory:
                return "Advisory";
            case PRS:
                return "PRS";
            default:
                return "Failure";
        }
    }

    public String getAddOrRemoveName() {
        return addOrRemoveName;
    }

    public String getAddOrRemovalType() {
        switch(defectType) {
            case Advisory:
                return "advisory";
            case PRS:
                return "PRS";
            default:
                return "failure";
        }
    }
}
