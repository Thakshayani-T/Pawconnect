<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'center') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');

if (!isset($_GET['id'])) {
    header("Location: center_dashboard.php");
    exit;
}

$pet_id = intval($_GET['id']);
$center_id = $_SESSION['user_id'];

// Verify pet belongs to logged-in center
$sql = "SELECT image FROM pets WHERE id = $pet_id AND added_by = $center_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Pet not found or not owned by this center
    header("Location: center_dashboard.php");
    exit;
}

$pet = $result->fetch_assoc();

// Delete image file if exists
$image_path = "../uploads/" . $pet['image'];
if (file_exists($image_path)) {
    unlink($image_path);
}

// Delete pet record
$conn->query("DELETE FROM pets WHERE id = $pet_id AND added_by = $center_id");

header("Location: center_dashboard.php?msg=Pet deleted successfully");
exit;
