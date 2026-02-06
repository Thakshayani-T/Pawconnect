<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
include('../includes/db.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Update adoption status to rejected
    $stmt = $conn->prepare("UPDATE adoptions SET status = 'rejected', responded_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Adoption request rejected.";
    header('Location: admin_dashboard.php?page=adoptions');
    exit;
} else {
    header('Location: admin_dashboard.php?page=adoptions');
    exit;
}
?>
