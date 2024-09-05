<?php
session_start();
include('config.php'); // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='signup.html';</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $sql = "INSERT INTO Patients (FirstName, LastName, DateOfBirth, Gender, Address, ContactNumber, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $last_name, $dob, $gender, $address, $contact_number, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='login.html';</script>";
    } else {
        echo "<script>alert('Registration failed. Please try again.'); window.location.href='signup.html';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
