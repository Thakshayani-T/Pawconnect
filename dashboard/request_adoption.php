<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'adopter') {
    header('Location: ../login.php');
    exit;
}

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id'])) {
    $pet_id = (int)$_POST['pet_id'];
    $adopter_id = (int)$_SESSION['user_id'];

    // 1. Check if pet exists and is available
    $stmt = $conn->prepare("SELECT sale_status FROM pets WHERE id = ?");
    $stmt->bind_param('i', $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result || $result->num_rows === 0) {
        $_SESSION['message'] = "Pet not found.";
        $stmt->close();
        header('Location: adopter_dashboard.php');
        exit;
    }
    $pet = $result->fetch_assoc();
    $stmt->close();

    if ($pet['sale_status'] !== 'available') {
        $_SESSION['message'] = "Sorry, this pet is not available for adoption.";
        header('Location: adopter_dashboard.php');
        exit;
    }

    // 2. Check if adopter already requested this pet
    $stmt = $conn->prepare("SELECT id FROM adoptions WHERE pet_id = ? AND adopter_id = ?");
    $stmt->bind_param('ii', $pet_id, $adopter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = ($result && $result->num_rows > 0);
    $stmt->close();

    if ($exists) {
        $_SESSION['message'] = "You have already requested adoption for this pet.";
        header('Location: adopter_dashboard.php');
        exit;
    }

    // 3. Insert adoption request
    $stmt = $conn->prepare("INSERT INTO adoptions (pet_id, adopter_id, status, adoption_date) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param('ii', $pet_id, $adopter_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Adoption request submitted successfully!";
    } else {
        $_SESSION['message'] = "Failed to submit adoption request: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

header('Location: adopter_dashboard.php');
exit;
?>
