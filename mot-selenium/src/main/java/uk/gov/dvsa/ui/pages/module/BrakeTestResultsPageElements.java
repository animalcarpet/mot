package uk.gov.dvsa.ui.pages.module;


import uk.gov.dvsa.helper.enums.BrakeTestConstants.FieldType;

public enum BrakeTestResultsPageElements {
    // BREAK CONTROLS
    CONTROL_ONE_FRONT("control1EffortFront", FieldType.Input), //Class 1
    CONTROL_ONE_FRONT_LOCK("control1LockFront", FieldType.Checkbox), //Class 1
    CONTROL_ONE_REAR("control1EffortRear", FieldType.Input), //Class 1
    CONTROL_ONE_REAR_LOCK("control1LockRear", FieldType.Checkbox), //Class 1
    CONTROL_ONE_SIDECAR("control1EffortSidecar", FieldType.Input), //Class 1

    CONTROL_TWO_FRONT("control2EffortFront", FieldType.Input), //Class 1
    CONTROL_TWO_FRONT_LOCK("control2LockFront", FieldType.Checkbox), //Class 1
    CONTROL_TWO_REAR("control2EffortRear", FieldType.Input), //Class 1
    CONTROL_TWO_REAR_LOCK("control2LockRear", FieldType.Checkbox), //Class 1
    CONTROL_TWO_SIDECAR("control2EffortSidecar", FieldType.Input), //Class 1
    // BREAK EFFICIENCY (Decelerometer)
    CONTROL_ONE_EFFICIENCY("control1BrakeEfficiency", FieldType.Input), //Class 1
    CONTROL_TWO_EFFICIENCY("control2BrakeEfficiency", FieldType.Input), //Class 1
    // GRADIENT EFFICIENCY (Gradient)
    CONTROL_ONE_ABOVE_30("gradientControl130% and above", FieldType.Radiobutton), //Class 1
    CONTROL_ONE_BETWEEN_30_AND_25("gradientControl1Between 25% and 30%",
            FieldType.Radiobutton), //Class 1
    CONTROL_ONE_BELOW_25("gradientControl125% and below", FieldType.Radiobutton), //Class 1
    CONTROL_TWO_ABOVE_30("gradientControl230% and above", FieldType.Radiobutton), //Class 1
    CONTROL_TWO_BETWEEN_30_AND_25("gradientControl2Between 25% and 30%",
            FieldType.Radiobutton), //Class 1
    CONTROL_TWO_BELOW_25("gradientControl225% and below", FieldType.Radiobutton), //Class 1
    // SERVICE BREAK
    SERVICE_BRAKE1_AXLE1_NEARSIDE("serviceBrakeEffortNearsideAxle1",
            FieldType.Input), //Class 3, 4, 5, 7
    SERVICE_BRAKE1_AXLE1_NEARSIDE_LOCK("serviceBrakeLockNearsideAxle1",
            FieldType.Checkbox), //Class 3, 4, 5, 7
    SERVICE_BRAKE1_AXLE1_OFFSIDE("serviceBrakeEffortOffsideAxle1",
            FieldType.Input), //Class 3, 4, 5, 7
    SERVICE_BRAKE1_AXLE1_OFFSIDE_LOCK("serviceBrakeLockOffsideAxle1",
            FieldType.Checkbox), //Class 3, 4, 5, 7
    SERVICE_BRAKE1_AXLE2_NEARSIDE("serviceBrakeEffortNearsideAxle2",
            FieldType.Input), //Class 4, 5, 7
    SERVICE_BRAKE1_AXLE2_NEARSIDE_LOCK("serviceBrakeLockNearsideAxle2",
            FieldType.Checkbox), //Class 4, 5, 7
    SERVICE_BRAKE1_AXLE2_OFFSIDE("serviceBrakeEffortOffsideAxle2", FieldType.Input), //Class 4, 5, 7
    SERVICE_BRAKE1_AXLE2_OFFSIDE_LOCK("serviceBrakeLockOffsideAxle2",
            FieldType.Checkbox), //Class 4, 5, 7
    SERVICE_BRAKE1_AXLE3_NEARSIDE("serviceBrakeEffortNearsideAxle3", FieldType.Input), //Class 4, 5
    SERVICE_BRAKE1_AXLE3_NEARSIDE_LOCK("serviceBrakeLockNearsideAxle3",
            FieldType.Checkbox), //Class 4, 5
    SERVICE_BRAKE1_AXLE3_OFFSIDE("serviceBrakeEffortOffsideAxle3", FieldType.Input), //Class 4, 5
    SERVICE_BRAKE1_AXLE3_OFFSIDE_LOCK("serviceBrakeLockOffsideAxle3",
            FieldType.Checkbox), //Class 4, 5
    SERVICE_BRAKE1_SINGLE_WHEEL_NEARSIDE("serviceBrakeEffortSingle", FieldType.Input), //Class 3
    SERVICE_BRAKE1_SINGLE_WHEEL_NEARSIDE_LOCK("serviceBrakeLockSingle",
            FieldType.Checkbox), //Class 3

    SERVICE_BRAKE2_AXLE1_NEARSIDE("serviceBrake2EffortNearsideAxle1", FieldType.Input), //Class 3
    SERVICE_BRAKE2_SINGLE_WHEEL_NEARSIDE("serviceBrake2EffortSingle", FieldType.Input), //Class 3
    SERVICE_BRAKE2_SINGLE_WHEEL_NEARSIDE_LOCK("serviceBrake2LockSingle",
            FieldType.Checkbox), //Class 3

    // SERVICE BREAK EFFICIENCY (Decelerometer)
    SERVICE_BRAKE1_EFFICIENCY("serviceBrake1Efficiency", FieldType.Input), //Class 3, 5, 7
    SERVICE_BRAKE2_EFFICIENCY("serviceBrake2Efficiency", FieldType.Input), //Class 3, 5
    // PARKING BREAK
    PARKING_SINGLE("parkingBrakeEffortSingle", FieldType.Input), //Class 3, 4, 5, 7
    PARKING_SINGLE_LOCK("parkingBrakeLockSingle", FieldType.Checkbox), //Class 3, 4, 5, 7
    PARKING_ONE_NEARSIDE("parkingBrakeEffortNearside", FieldType.Input), //Class 4, 5, 7
    PARKING_ONE_NEARSIDE_LOCK("parkingBrakeLockNearside", FieldType.Checkbox), //Class 4, 5, 7
    PARKING_ONE_OFFSIDE("parkingBrakeEffortOffside", FieldType.Input), //Class 4, 5, 7
    PARKING_ONE_OFFSIDE_LOCK("parkingBrakeLockOffside", FieldType.Checkbox), //Class 4, 5, 7
    PARKING_TWO_NEARSIDE("parkingBrakeEffortSecondaryNearside", FieldType.Input), //Class 4
    PARKING_TWO_NEARSIDE_LOCK("parkingBrakeLockSecondaryNearside", FieldType.Checkbox), //Class 4
    PARKING_TWO_OFFSIDE("parkingBrakeEffortSecondaryOffside", FieldType.Input), //Class 4
    PARKING_TWO_OFFSIDE_LOCK("parkingBrakeLockSecondaryOffside", FieldType.Checkbox), //Class 4
    // PARKING BREAK EFFICIENCY (Decelerometer)
    PARKING_BRAKE_EFFICIENCY_DECELEROMETER("parkingBrakeEfficiency",
            FieldType.Input), //Class 4, 5, 7
    // PARKING BREAK EFFICIENCY (Gradient)
    PARKING_BRAKE_EFFICIENCY_GRADIENT_PASS("parkingBrakeEfficiencyPassPass",
            FieldType.Radiobutton), //Class 3, 4, 5, 7
    PARKING_BRAKE_EFFICIENCY_GRADIENT_FAIL("parkingBrakeEfficiencyPassFail",
            FieldType.Radiobutton); //Class 3, 4, 5, 7

    private final String id;
    private final FieldType fieldType;


    BrakeTestResultsPageElements(String id, FieldType fieldType) {
        this.id = id;
        this.fieldType = fieldType;
    }

    public String getId() {
        return id;
    }

    public FieldType getFieldType() {
        return fieldType;
    }
}
