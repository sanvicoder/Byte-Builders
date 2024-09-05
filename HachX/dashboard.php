<?php
session_start();
include('config.php'); 

$patient_id = $_SESSION['PatientID']; 
$section = isset($_GET['section']) ? $_GET['section'] : '';

// Fetch medication with refill count less than 5
$reminder_sql = "SELECT DrugName, RefillCount FROM Medications WHERE PatientID = '$patient_id' AND RefillCount < 5";
$reminder_result = $conn->query($reminder_sql);

$reminders = [];
if ($reminder_result->num_rows > 0) {
    while ($row = $reminder_result->fetch_assoc()) {
        $reminders[] = [
            'drug' => $row['DrugName'],
            'refill_count' => $row['RefillCount']
        ];
    }
}

$sql = "";
$title = "";
$data = "";

switch ($section) {
    case 'details':
        $sql = "SELECT * FROM Patients WHERE PatientID = '$patient_id'";
        $title = "Patient Details";
        break;
    case 'visits':
        $sql = "SELECT * FROM Visits WHERE PatientID = '$patient_id'";
        $title = "Visits";
        break;
    case 'medical_history':
        $sql = "SELECT * FROM MedicalHistory WHERE PatientID = '$patient_id'";
        $title = "Medical History";
        break;
    case 'current_medications':
        $sql = "SELECT * FROM Medications WHERE PatientID = '$patient_id'";
        $title = "Current Medications";
        break;
    case 'immunizations':
        $sql = "SELECT * FROM Immunizations WHERE PatientID = '$patient_id'";
        $title = "Immunizations";
        break;
    case 'vitals':
        $sql = "SELECT * FROM VitalSigns WHERE PatientID = '$patient_id'";
        $title = "Vitals";
        break;
    case 'lab_results':
        $sql = "SELECT * FROM LabResults WHERE PatientID = '$patient_id'";
        $title = "Lab Test Results";
        break;
    case 'clinical_notes':
        $sql = "SELECT * FROM ClinicalNotes WHERE PatientID = '$patient_id'";
        $title = "Clinical Notes";
        break;
    default:
        $title = "Patient Dashboard";
        $data = "<p class='info-text'>Please select an option to view details.</p>";
        break;
}

if ($sql) {
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $data = "<div class='table-container'><table class='table table-striped table-bordered'><thead><tr>";
        // Print table headers based on column names
        $columns = $result->fetch_fields();
        foreach ($columns as $column) {
            $data .= "<th>" . htmlspecialchars($column->name) . "</th>";
        }
        $data .= "</tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            $data .= "<tr>";
            foreach ($columns as $column) {
                $data .= "<td>" . htmlspecialchars($row[$column->name]) . "</td>";
            }
            $data .= "</tr>";
        }
        $data .= "</tbody></table></div>";
    } else {
        $data = "<p>No data found.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="header-bar">
            <div class="container">
                <h2 class="mb-0">Patient Dashboard</h2>
                <p class="mb-0">Patient ID: <?php echo htmlspecialchars($patient_id); ?></p>
            </div>
        </div>
        
        <!-- Reminder Section -->
        <div class="reminder-section container mt-3">
            <?php if (!empty($reminders)) { ?>
                <div class="alert alert-warning">
                    <h4>Medication Reminders</h4>
                    <ul>
                        <?php foreach ($reminders as $reminder) { ?>
                            <li>You have <strong><?php echo $reminder['refill_count']; ?></strong> days of <strong><?php echo $reminder['drug']; ?></strong> left.</li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
        </div>

        <div class="main-content container mt-4">
            <div class="row">
                <div class="col-md-3">
                    <p class="info-text">Please select an option to view details:</p>
                    <div class="btn-group-vertical">
                        <a href="dashboard.php?section=details" class="btn btn-outline-primary mb-2">Patient Details</a>
                        <a href="dashboard.php?section=visits" class="btn btn-outline-primary mb-2">Visits</a>
                        <a href="dashboard.php?section=medical_history" class="btn btn-outline-primary mb-2">Medical History</a>
                        <a href="dashboard.php?section=current_medications" class="btn btn-outline-primary mb-2">Current Medications</a>
                        <a href="dashboard.php?section=immunizations" class="btn btn-outline-primary mb-2">Immunizations</a>
                        <a href="dashboard.php?section=vitals" class="btn btn-outline-primary mb-2">Vitals</a>
                        <a href="dashboard.php?section=lab_results" class="btn btn-outline-primary mb-2">Lab Test Results</a>
                        <a href="dashboard.php?section=clinical_notes" class="btn btn-outline-primary mb-2">Clinical Notes</a>
                        <!-- Redirect Insights button to insights.html -->
                        <a href="insights.html" class="btn btn-outline-primary mb-2">Insights</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <?php echo $data; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to trigger refill count update every 24 hours -->
    <script>
        setInterval(function() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_refill_count.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("patient_id=<?php echo $patient_id; ?>");
        }, 24 * 60 * 60 * 1000); // 24 hours in milliseconds
    </script>
</body>
</html>
