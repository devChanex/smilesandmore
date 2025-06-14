<?php
//Service for Registration

require_once('databaseService.php');

$clientId = urldecode($_POST['clientId']);

//echo'<script>alert("tesT");</script>';
//INHERITANCE -- CREATING NEW INSTANCE OF A CLASS (INSTANTIATE)
$service = new ServiceClass();
$result = $service->loadMedHistory($clientId);
//USE THIS AS YOUR BASIS
class ServiceClass
{

    private $conn;
    public function __construct()
    {
        $database = new Database();
        $db = $database->dbConnection();
        $this->conn = $db;
    }

    public function runQuery($sql)
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt;
    }
    public function loadMedHistory($clientId)
    {
        //:a,:b parameter
        try {
            $query = "select * from medhistoryv2 where clientId=:clientid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':clientid', $clientId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo ' 
                    
                                        <h6 class="mb-3">Dental History</h6>
                                       ';
                    echo '

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        Previous Dentist:
        &nbsp;<strong>' . (!empty($row["prevDentist"]) && $row["prevDentist"] !== "null" ? htmlspecialchars($row["prevDentist"]) : "<em>Not specified</em>") . '</strong>
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        Last Dental Visit:
        &nbsp;<strong>' . (!empty($row["lastDentalVisit"]) && $row["lastDentalVisit"] !== "null" ? htmlspecialchars($row["lastDentalVisit"]) : "<em>Not specified</em>") . '</strong>
    </div>
</div>

<h6 class="mb-3">Medical History</h6>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        Physician:
        &nbsp;<strong>' . (!empty($row["physician"]) && $row["physician"] !== "null" ? htmlspecialchars($row["physician"]) : "<em>Not specified</em>") . '</strong>
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        Specialty:
        &nbsp;<strong>' . (!empty($row["specialty"]) && $row["specialty"] !== "null" ? htmlspecialchars($row["specialty"]) : "<em>Not specified</em>") . '</strong>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        Office Address:
        &nbsp;<strong>' . (!empty($row["officeAddress"]) && $row["officeAddress"] !== "null" ? htmlspecialchars($row["officeAddress"]) : "<em>Not specified</em>") . '</strong>
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        Office Number:
        &nbsp;<strong>' . (!empty($row["officeNumber"]) && $row["officeNumber"] !== "null" ? htmlspecialchars($row["officeNumber"]) : "<em>Not specified</em>") . '</strong>
    </div>
</div>

<hr>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        1. Are you in good health?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["goodHealth"] === "yes" ? "Yes" :
                        ($row["goodHealth"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        2. Are you under medical treatment now?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["underTreatment"] === "yes" ? "Yes" :
                        ($row["underTreatment"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - If so, what is the condition being treated?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["treatmentCondition"] === "null" || empty($row["treatmentCondition"])
                        ? "<em>None specified</em>"
                        : htmlspecialchars($row["treatmentCondition"])
                    ) . '</strong>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        3. Have you ever had serious illness or surgical operation?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["seriousIllness"] === "yes" ? "Yes" :
                        ($row["seriousIllness"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - If so, what illness or operation?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["illnessCondition"] === "null" || empty($row["illnessCondition"])
                        ? "<em>None specified</em>"
                        : htmlspecialchars($row["illnessCondition"])
                    ) . '</strong>
    </div>
</div>


<!-- 4. Have you ever been hospitalized? -->
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        4. Have you ever been hospitalized?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["hospitalized"] === "yes" ? "Yes" :
                        ($row["hospitalized"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - If so, when and why?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["hospitalizedCondition"] === "null" || empty($row["hospitalizedCondition"])
                        ? "<em>None specified</em>"
                        : htmlspecialchars($row["hospitalizedCondition"])
                    ) . '</strong>
    </div>
</div>

<!-- 5. Are you taking any medication? -->
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        5. Are you taking any prescription/non-prescription medication?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["medication"] === "yes" ? "Yes" :
                        ($row["medication"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - If so, please specify:
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["medicationCondition"] === "null" || empty($row["medicationCondition"])
                        ? "<em>None specified</em>"
                        : htmlspecialchars($row["medicationCondition"])
                    ) . '</strong>
    </div>
</div>

<!-- 6. Do you use tobacco products? -->
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        6. Do you use tobacco products?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["tobaccoUse"] === "yes" ? "Yes" :
                        ($row["tobaccoUse"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

<!-- 7. Do you use alcohol, cocaine or other dangerous drugs? -->
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        7. Do you use alcohol, cocaine or other dangerous drugs?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["substanceUse"] === "yes" ? "Yes" :
                        ($row["substanceUse"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

              ';

                    $allergyValues = isset($row["allergiesCondition"]) ? explode(",", $row["allergiesCondition"]) : [];
                    echo '
                                       
<!-- 8. Are you allergic to any of the following -->
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        8. Are you allergic to any of the following:
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                        $row["allergies"] === "yes" ? "Yes" :
                        ($row["allergies"] === "no" ? "No" : "<em>Not specified</em>")
                    ) . '</strong>
    </div>
</div>

' . (
                        $row["allergies"] === "yes"
                        ? '
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - If yes, specify:
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . (
                            empty($row["allergiesCondition"]) || $row["allergiesCondition"] === "null"
                            ? "<em>None specified</em>"
                            : htmlspecialchars(implode(", ", array_map("trim", explode(",", $row["allergiesCondition"]))))
                        ) . '</strong>
    </div>
</div>' .
                        (in_array("Others", $allergyValues) && !empty($row["otherAllergyField"]) && $row["otherAllergyField"] !== "null"
                            ? '
<div class="row mb-2">
    <div class="col-lg-6 d-flex align-items-center">
        - Other allergies:
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . htmlspecialchars($row["otherAllergyField"]) . '</strong>
    </div>
</div>'
                            : '')
                        : ''
                    ) . '


                                      <!-- 9. For women only -->
<div class="row mb-2">
    <div class="col-lg-12 fw-bold">
        9. For women only:
    </div>

    <!-- Are you pregnant? -->
    <div class="col-lg-6 d-flex align-items-center">
        Are you pregnant?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . ($row["pregnant"] === "yes" ? "Yes" : ($row["pregnant"] === "no" ? "No" : "<em>Not specified</em>")) . '</strong>
    </div>

    <!-- Are you nursing? -->
    <div class="col-lg-6 d-flex align-items-center">
        Are you nursing?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . ($row["nursing"] === "yes" ? "Yes" : ($row["nursing"] === "no" ? "No" : "<em>Not specified</em>")) . '</strong>
    </div>

    <!-- Are you taking birth control pills? -->
    <div class="col-lg-6 d-flex align-items-center">
        Are you taking birth control pills?
    </div>
    <div class="col-lg-6 d-flex align-items-center">
        <strong>' . ($row["birthControl"] === "yes" ? "Yes" : ($row["birthControl"] === "no" ? "No" : "<em>Not specified</em>")) . '</strong>
    </div>
</div>

<hr>
                                     ';

                    $medicalFlags = isset($row['medicalHistory']) ? explode(',', $row['medicalHistory']) : [];
                    $medicalHistoryOther = isset($row['medicalHistoryOther']) ? htmlspecialchars($row['medicalHistoryOther']) : '';

                    $columns = [
                        [
                            "High Blood Pressure",
                            "Low Blood Pressure",
                            "Epilepsy / Convulsions",
                            "AIDS or HIV Infection",
                            "Sexually Transmitted disease",
                            "Stomach Troubles / Ulcers",
                            "Fainting Seizure",
                            "Rapid Weight Loss",
                            "Heart Disease",
                            "Heart Murmur",
                            "Hepatitis / Liver Disease",
                            "Rheumatic Fever",
                            "Hay Fever / Allergies"
                        ],
                        [
                            "Respiratory Problems",
                            "Hepatitis / Jaundice",
                            "Tuberculosis",
                            "Radiation Therapy",
                            "Swollen ankles",
                            "Joint Replacement / Implant",
                            "Kidney disease",
                            "Heart Surgery",
                            "Diabetes",
                            "Heart Attack",
                            "Chest pain",
                            "Thyroid Problem",
                            "Stroke"
                        ],
                        [
                            "Cancer / Tumors",
                            "Anemia",
                            "Angina",
                            "Asthma",
                            "Emphysema",
                            "Bleeding Problems",
                            "Blood Diseases",
                            "Head Injuries",
                            "Arthritis / Rheumatism",
                            "Rapid Weight Loss",
                            "Other"
                        ]
                    ];

                    echo '<label class="form-label"><strong>Medical Conditions:</strong></label>';
                    echo '<div class="row text-justify" style=" line-height: 1.5;">'; // compact text
                    $flagIndex = 0;

                    foreach ($columns as $col) {
                        echo '<div class="col-md-4" style="white-space: nowrap;">';
                        foreach ($col as $condition) {
                            $icon = (isset($medicalFlags[$flagIndex]) && $medicalFlags[$flagIndex] === 'true') ? '✅' : '❌';
                            $label = htmlspecialchars($condition);

                            if ($label === 'Other') {
                                if (isset($medicalFlags[$flagIndex]) && $medicalFlags[$flagIndex] === 'true' && !empty($medicalHistoryOther)) {
                                    echo $icon . ' <strong>Other:</strong> ' . $medicalHistoryOther . '<br>';
                                } else {
                                    echo '❌ <strong>Other</strong><br>';
                                }
                            } else {
                                echo $icon . ' ' . $label . '<br>';
                            }

                            $flagIndex++;
                        }
                        echo '</div>';
                    }

                    echo '</div>';





                }
            }

        } catch (Exception $e) {
            return "Error:" . $e->getMessage();
        }


    }
    //UNTIL THIS CODE

}
//UNTIL HERE COPY



?>