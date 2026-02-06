<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'adopter') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: adopter_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pet_id'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: adopter_dashboard.php');
    exit;
}

$adopter_id = (int)$_SESSION['user_id'];
$pet_id = (int)$_POST['pet_id'];

/* Check pet availability */
$pet = $conn->query("SELECT sale_status FROM pets WHERE id=$pet_id")->fetch_assoc();
if (!$pet || $pet['sale_status'] !== 'available') {
    $_SESSION['error'] = 'Pet is no longer available.';
    header('Location: adopter_dashboard.php');
    exit;
}

/* Prevent duplicate requests */
$check = $conn->query("
    SELECT id FROM adoptions 
    WHERE pet_id=$pet_id AND adopter_id=$adopter_id
");
if ($check->num_rows > 0) {
    $_SESSION['error'] = 'You already requested this pet.';
    header('Location: adopter_dashboard.php');
    exit;
}

/* Insert adoption request */
$stmt = $conn->prepare("
    INSERT INTO adoptions (pet_id, adopter_id, status, requested_at)
    VALUES (?, ?, 'pending', NOW())
");
$stmt->bind_param('ii', $pet_id, $adopter_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Adoption request sent successfully.';
} else {
    $_SESSION['error'] = 'Failed to send adoption request.';
}

$stmt->close();
header('Location: adopter_dashboard.php');
exit;
