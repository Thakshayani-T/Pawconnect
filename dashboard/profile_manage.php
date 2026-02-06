<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'center') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');
$center_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET name='$name', email='$email' WHERE id='$center_id'");
    header("Location: center_dashboard.php?msg=Profile updated successfully!");
    exit;
}

$result = $conn->query("SELECT * FROM users WHERE id='$center_id'");
$center = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Profile</title>
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .profile-container {
            background: #fff;
            width: 400px;
            margin: 100px auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <h2>Manage Profile</h2>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($center['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($center['email']) ?>" required>
        <button type="submit">Update</button>
    </form>
    <a href="center_dashboard.php" class="back">← Back to Dashboard</a>
</div>
</body>
</html>
