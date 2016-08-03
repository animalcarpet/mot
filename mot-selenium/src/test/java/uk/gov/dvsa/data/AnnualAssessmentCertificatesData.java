package uk.gov.dvsa.data;

import uk.gov.dvsa.domain.model.AnnualAssessmentCertificate;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.AnnualAssessmentCertificateService;

import java.io.IOException;

public class AnnualAssessmentCertificatesData extends AnnualAssessmentCertificateService {

    public AnnualAssessmentCertificatesData() {}

    public AnnualAssessmentCertificate createAnnualAssessmentCertificateForGroupA(
        User user,
        String certificateNumber,
        String dateOfExam,
        String score) throws IOException {

        return createAnnualAssessmentCertificate(user, GROUP_A, certificateNumber, dateOfExam, score);
    }

    public AnnualAssessmentCertificate createAnnualAssessmentCertificateForGroupB(
        User user,
        String certificateNumber,
        String dateOfExam,
        String score) throws IOException {

        return createAnnualAssessmentCertificate(user, GROUP_B, certificateNumber, dateOfExam, score);
    }
}
