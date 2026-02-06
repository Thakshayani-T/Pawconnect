<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

include('../includes/db.php'); // make sure $conn is properly set here

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Update adoption status to approved
    $stmt = $conn->prepare("UPDATE adoptions SET status = 'approved', updated_at = NOW() WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed (update adoption): " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // 2. Get pet_id for this adoption
    $stmt = $conn->prepare("SELECT pet_id FROM adoptions WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed (select pet_id): " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result === false || $result->num_rows === 0) {
        $_SESSION['error'] = "Adoption request not found.";
        $stmt->close();
        header('Location: admin_dashboard.php?page=adoptions');
        exit;
    }
    $adoption = $result->fetch_assoc();
    $stmt->close();

    // 3. Update pet status to 'adopted'
    $pet_id = $adoption['pet_id'];
    $stmt = $conn->prepare("UPDATE pets SET sale_status = 'adopted' WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed (update pet): " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param('i', $pet_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Adoption approved successfully.";
    header('Location: admin_dashboard.php?page=adoptions');
    exit;
} else {
    header('Location: admin_dashboard.php?page=adoptions');
    exit;
}
?>
