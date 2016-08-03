package uk.gov.dvsa.domain.model;

public class AnnualAssessmentCertificate {
    protected String id;
    protected String vehicleClassGroupCode;
    protected String certificateNumber;
    protected String examDate;
    protected String score;

    public AnnualAssessmentCertificate(){}

    public AnnualAssessmentCertificate(
            String examDate,
            String vehicleClassGroupCode,
            String certificateNumber,
            String score,
            String id
    ){
        this.id = id;
        this.score = score;
        this.vehicleClassGroupCode = vehicleClassGroupCode;
        this.certificateNumber = certificateNumber;
        this.examDate = examDate;
    }

    public String getId() {
        return id;
    }

    public String getScore() {
        return score;
    }

    public String getVehicleClassGroupCode() {
        return vehicleClassGroupCode;
    }

    public String getCertificateNumber() {
        return certificateNumber;
    }

    public String getExamDate() {
        return examDate;
    }
}
