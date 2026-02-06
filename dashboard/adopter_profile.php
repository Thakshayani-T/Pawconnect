<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'adopter') {
    header('Location: ../login.php');
    exit;
}
include('../includes/db.php');

$adopter_id = (int)$_SESSION['user_id'];

// Fetch adopter details
$user_result = $conn->query("SELECT * FROM users WHERE id = $adopter_id");
$user = $user_result->fetch_assoc();

$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Optional: only update if provided

    if ($name === '' || $email === '') {
        $update_msg = '<div class="alert alert-danger">Name and Email cannot be empty.</div>';
    } else {
        $name_esc = $conn->real_escape_string($name);
        $email_esc = $conn->real_escape_string($email);

        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$name_esc', email='$email_esc', password='$password_hash' WHERE id=$adopter_id";
        } else {
            $sql = "UPDATE users SET name='$name_esc', email='$email_esc' WHERE id=$adopter_id";
        }

        if ($conn->query($sql)) {
            $update_msg = '<div class="alert alert-success">Profile updated successfully.</div>';
            // Refresh user data
            $user_result = $conn->query("SELECT * FROM users WHERE id = $adopter_id");
            $user = $user_result->fetch_assoc();
        } else {
            $update_msg = '<div class="alert alert-danger">Error updating profile: ' . $conn->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Adopter Profile - PawConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: url('../uploads/background-bc3.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    margin: 0; color: #333;
}
.sidebar {
    position: fixed;
    top: 0; left: 0; width: 250px; height: 100vh;
    background: #004aad; color: #fff;
    display: flex; flex-direction: column; padding: 20px 0;
}
.sidebar img.logo { max-width: 150px; margin: 0 auto 20px; }
.sidebar a {
    display: block; padding: 12px 20px; color: #e0e7ff;
    text-decoration: none; font-weight: 500; transition: 0.3s;
}
.sidebar a:hover, .sidebar a.active { background: #1e40af; border-left: 4px solid #facc15; }
.logout-btn { background: #dc2626; color: #fff; text-align: center; padding: 10px; margin: 20px; border-radius: 6px; text-decoration: none; }
.logout-btn:hover { background: #b91c1c; }
main { margin-left: 250px; padding: 2rem; background: rgba(255,255,255,0.95); min-height: 100vh; }
h2 { color: #1d4ed8; margin-bottom: 20px; }
form { max-width: 600px; background: #f1f5f9; padding: 20px; border-radius: 10px; }
.btn-primary { background: #2563eb; border: none; }
.btn-primary:hover { background: #1e40af; }
</style>
</head>
<body>
<div class="sidebar">
    <img src="../uploads/logo.png" alt="PawConnect" class="logo">
    <a href="adopter_dashboard.php">Dashboard</a>
    <a href="adopter_profile.php" class="active">Manage Profile</a>
    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

<main>
    <h2>Manage Profile</h2>
    <?= $update_msg ?>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</main>
</body>
</html>
