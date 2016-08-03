package uk.gov.dvsa.helper;

public class ReasonForRejection {
    // Lamps, Reflectors and Electrical Equipment >> Horn
    public static final ReasonForRejection HORN_CONTROL_MISSING =
            new ReasonForRejection(7162, "Lamps, Reflectors and Electrical Equipment > Horn",
                    "control missing", "");
    public static final ReasonForRejection HORN_CONTROL_INSECURE =
            new ReasonForRejection(7163, "Lamps, Reflectors and Electrical Equipment > Horn",
                    "control insecure", "");
    // Lamps, Reflectors and Electrical Equipment >> Electrical wiring
    public static final ReasonForRejection ELECTRICAL_WIRING_DAMAGED_CAUSE_SHORT =
            new ReasonForRejection(829,
                    "Lamps, Reflectors and Electrical Equipment > Electrical wiring",
                    "damaged, likely to cause a short", "damaged, but not likely to cause a short");
    public static final ReasonForRejection ELECTRICAL_WIRING_DAMAGE_WIRED_EXPOSED =
            new ReasonForRejection(1522, "Anti-roll bars > Pins / bushes / ball joints",
                    "damaged and bare wires exposed", "damaged, but not exposing bare wires");
    // Suspension, Anti-roll bars, Pins bushes/ball joints
    public static final ReasonForRejection BALLJOINT_EXCESSIVELY_DETERIORATED =
            new ReasonForRejection(1094, "Anti-roll bars > Pins / bushes / ball joints",
                    "ball joint dust cover excessively deteriorated so that it no longer prevents the ingress of dirt",
                    "ball joint dust cover deteriorated, but preventing the ingress of dirt");
    // Brakes, Hub components, Brake linings
    public static final ReasonForRejection BRAKE_LININGS_LESS_THAN_1_5_THICK =
            new ReasonForRejection(8291, "Hub components > Brake linings", "less than 1.5 mm thick",
                    "wearing thin");
    // Brakes, Brake performance, Brake performance not tested
    public static final ReasonForRejection BRAKE_PERFORMANCE_NOT_TESTED =
            new ReasonForRejection(10102, "Brake performance > Brake performance", "not tested",
                    "");
    // Tyres, Tread depth, Vehicles first used after 2 January 1933 upto 9 seats including driver
    public static final ReasonForRejection
            VEHICLES_1ST_USE_AFTER_2_JANUARY_TREAD_DEPTH_BELOW_REQUIREMENTS =
            new ReasonForRejection(8394,
                    "Tread depth > Vehicles first used after 2 January 1933 upto 9 seats including driver",
                    "tread depth below requirements of 1.6mm", "worn close to the legal limit");
    // Mounting
    public static final ReasonForRejection MOUNTING_EXCESSIVELY_DETERIORATED_FLEXIBLE_PIPE =
            new ReasonForRejection(214, "Handlebars > Mounting",
                    "excessively deteriorated (flexible type)", "slightly deteriorated");
    //Warning Lamp
    public static final ReasonForRejection WARNING_LAMP_MISSING =
            new ReasonForRejection(114, "Motorcycle lighting and signalling > Stop lamp", "missing",
                    "");
    public static final ReasonForRejection EMISSIONS_EXEMPT_FROM_EMISSIONS_TESTING_END_DATED =
            new ReasonForRejection(9151, "Motor tricycles and quadricycles > Emissions",
                    "Do not use. All tricycles and quadricycles are exempt from emissions testing.",
                    "");
    public static final ReasonForRejection ELECTRICAL_WIRING_DETERIORATED_CAUSE_SHORT =
            new ReasonForRejection(833,
                    "Lamps, Reflectors and Electrical Equipment > Electrical wiring",
                    "deteriorated, likely to cause a short", "DETERIORATED, BUT NOT LIKEY TO CAUSE A SHORT");
    public static final ReasonForRejection DOOR_PILLAR_DETERIORATED =
            new ReasonForRejection(2044, "Doors > Drivers",
                    "pillar deteriorated so the door cannot be readily opened or closed", "PILLAR DETERIORATED BUT THE DOOR CAN STILL BE READILY OPENED AND CLOSED");
    public static final ReasonForRejection BREAK_CONTROL_SHORT =
            new ReasonForRejection(9379, "Brake controls > Handlebar lever",
                    "is so short so that the brake cannot be readily applied ", "IS SHORT BUT BRAKE CAN STILL BE READILY APPLIED");
    public static final ReasonForRejection ROAD_WHEELS_WORN_HUB_SPIGOT =
            new ReasonForRejection(4341, "Road Wheels > Condition",
                    "fitment excessively worn between the wheel and hub spigot mounting","fitment has slight wear between wheel and hub spigot mounting.");
    public static final ReasonForRejection SERIOUSLY_DAMAGED = new ReasonForRejection(8222, "Components > Pipes",
            "seriously damaged (found during steering lock to lock check) ","slightly damaged");
    public static final ReasonForRejection HOSES_MODIFIED =
            new ReasonForRejection(2505, "Steering system > Ball joint",
                    "inappropriately modified","modified, but not inappropriately");
    public static final ReasonForRejection EXCESSIVELY_WEAKENED_CORROSION =
            new ReasonForRejection(4114, "Components > Cylinders","excessively weakened by corrosion","CORRODED BUT NOT EXCESSIVELY WEAKENED");
    public static final ReasonForRejection BRACKET_MODIFIED_WEAKENED =
            new ReasonForRejection(7712, "Tie bars / rods > Attachment","attachment bracket modified and seriously weakened","attachment bracket modified but not seriously weakened");
    public static final ReasonForRejection TREAD_DEPTH_BELOW_1_0 =
            new ReasonForRejection(8587, "Tread depth > Vehicles first used before 3 January 1933","tread depth below requirements of 1.0mm","worn close to the legal limit");
    public static final ReasonForRejection WARNING_LAMP_INDICATES_FAULT =
            new ReasonForRejection(6012, "Supplementary restraint systems > SRS Malfunction Indicator Lamp","warning lamp indicates a fault","warning lamp does not illuminate");
    public static final ReasonForRejection AFFECTED_OPERATION_ANOTHER_LAMP =
            new ReasonForRejection(7049, "Rear fog lamp > Fog lamp","adversely affected by the operation of another lamp","affected by the operation of another lamp");
    public static final ReasonForRejection SERIOUSLY_MISALIGNED =
            new ReasonForRejection(7279, "Steering system > Drag link end","seriously misaligned","slightly misaligned");
    public static final ReasonForRejection WIRES_BROKEN_SIGNIFICANTLY_WEAKENED =
            new ReasonForRejection(8098, "Mechanical components > Cable","has wires broken and is significantly weakened ","has wires broken but is not significantly weakened");
    public static final ReasonForRejection DIAL_GLASS_BROKEN =
            new ReasonForRejection(4568, "Body, Structure and General Items > Speedometer","dial glass broken","dial glass slightly damaged");
    public static final ReasonForRejection BRACKET_EXCESSIVELY_WORN =
            new ReasonForRejection(691, "Towbars > Adjustable towbar","bracket excessively worn","bracket slightly worn");
    public static final ReasonForRejection TOP_BEARING_EXCESSIVELY_WORN =
            new ReasonForRejection(7185, "Steering control > Steering column","top bearing excessively worn","top bearing slightly worn");
    public static final ReasonForRejection SLIPPER_EXCESSIVELY_WORN =
            new ReasonForRejection(3436, "Leaf springs > Spring slippers","slipper excessively worn","slipper slightly worn");
    public static final ReasonForRejection EXCESSIVE_PLAY_RECEIVER_SOCKET =
            new ReasonForRejection(687, "Towbars > Detachable tow ball","has excessive play at receiver socket","has slight play at receiver socket");
    public static final ReasonForRejection DAMAGED_LEAD_CANNOT_BE_SECURELY_CONNECTED =
            new ReasonForRejection(836, "Lamps, Reflectors and Electrical Equipment > Trailer electrical socket","damaged so that lead cannot be securely connected","damaged, but the lead can still be securely connected");
    public static final ReasonForRejection DETERIORATED_CANNOT_FUNCTION =
            new ReasonForRejection(7553, "Bonded suspension > Condition","deteriorated and cannot function as intended","deteriorated but can function as intended");


    public final int reasonId;
    public final String testItemCategoryName;
    public final String reasonDescription;
    public final String advisoryText;

    public ReasonForRejection(int reasonId, String testItemCategoryName, String reasonDescription,
                              String advisoryText) {
        super();
        this.reasonId = reasonId;
        this.testItemCategoryName = testItemCategoryName;
        this.reasonDescription = reasonDescription;
        this.advisoryText = advisoryText;
    }

}
