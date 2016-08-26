package uk.gov.dvsa.helper;

import uk.gov.dvsa.domain.model.mot.Defect;

import java.io.IOException;

public class DefectsTestsDataProvider {

    public static Object[][] getDefectArray() throws IOException {
        Object[][] defects = new Object[3][1];
        Defect.DefectBuilder builder = new Defect.DefectBuilder();

        defects[0][0] = buildAdvisoryDefect(builder, false);
        defects[1][0] = buildPRSDefect(builder);
        defects[2][0] = buildFailureDefect(builder);

        return defects;
    }

    public static Object[][] getAdvisoryDefect() throws IOException {
        Object[][] defect = new Object[1][1];

        defect[0][0] = buildAdvisoryDefect(new Defect.DefectBuilder(), true);

        return defect;
    }

    private static Defect buildFailureDefect(Defect.DefectBuilder builder) {
        builder.setCategoryPath(new String[] {"Drivers view of the road", "Windscreen"});
        builder.setDefectName("is of a temporary type");
        builder.setDefectType(Defect.DefectType.Failure);
        builder.setAddOrRemoveName("Windscreen is of a temporary type");
        builder.setIsDangerous(false);
        return builder.build();
    }

    private static Defect buildPRSDefect(Defect.DefectBuilder builder) {
        builder.setCategoryPath(new String[] {"Tyres", "Condition"});
        builder.setDefectName("has ply or cords exposed");
        builder.setDefectType(Defect.DefectType.PRS);
        builder.setAddOrRemoveName("Tyre has ply or cords exposed");
        builder.setIsDangerous(false);
        return builder.build();
    }

    private static Defect buildAdvisoryDefect(Defect.DefectBuilder builder, Boolean defectIsAdded) {
        builder.setCategoryPath(new String[] {"Brakes", "Brake performance", "Decelerometer", "Brake operation"});
        builder.setDefectName("grabbing slightly");
        builder.setDefectType(Defect.DefectType.Advisory);

        //defect success message is different for defect added vs defect edited
        if (defectIsAdded) {
            builder.setAddOrRemoveName("Brake operation grabbing slightly");
        } else {
            builder.setAddOrRemoveName("Service brake grabbing slightly");
        }

        builder.setIsDangerous(false);
        return builder.build();
    }
}