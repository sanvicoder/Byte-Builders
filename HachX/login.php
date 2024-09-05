<?php
session_start();
include('config.php'); // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_POST['PatientID'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Patients WHERE PatientID = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $patient_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['PatientID'] = $patient_id;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid Patient ID or Password');</script>";
    }

    $stmt->close();
}

$conn->close();
?>