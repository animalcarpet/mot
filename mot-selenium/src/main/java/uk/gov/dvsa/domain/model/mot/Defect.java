package uk.gov.dvsa.domain.model.mot;

public class Defect {

    public enum DefectType {Failure, PRS, Advisory};

    private String[] categoryPath;
    private String defectName;
    private DefectType defectType;
    private String addOrRemoveName;
    private Boolean isDangerous;

    private Defect(DefectBuilder builder) {
        this.categoryPath = builder.categoryPath;
        this.defectName = builder.defectName;
        this.defectType = builder.defectType;
        this.addOrRemoveName = builder.addOrRemoveName;
        this.isDangerous = builder.isDangerous;
    }

    public static class DefectBuilder {

        private String[] categoryPath;
        private String defectName;
        private DefectType defectType;
        private String addOrRemoveName;
        private Boolean isDangerous;

        public void setCategoryPath (String[] categoryPath) { this.categoryPath = categoryPath; }

        public void setDefectName (String defectName) { this.defectName = defectName; }

        public void setDefectType (DefectType defectType) { this.defectType = defectType; }

        public void setAddOrRemoveName (String addOrRemoveName) { this.addOrRemoveName = addOrRemoveName; }

        public void setIsDangerous (Boolean isDangerous) { this.isDangerous = isDangerous; }

        public Defect build (){
            return new Defect(this);
        }
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

    public void setIsDangerous(Boolean isDangerous) {
        this.isDangerous = isDangerous;
    }

    public Boolean getIsDangerous() {
        return this.isDangerous;
    }
}
